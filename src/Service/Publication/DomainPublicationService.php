<?php

declare(strict_types=1);

namespace App\Domaining\Service\Publication;

use App\Domaining\Dto\DomainPublicationSnapshot;
use App\Domaining\Dto\DomainRoutingIntent;
use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainPublicationState;
use App\Domaining\Entity\DomainRoutingTarget;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Publication\DomainPublicationServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\ServiceInterface\State\DomainBindingTransitionGuardInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainPublicationService implements DomainPublicationServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainRoutingTargetRepository $targetRepository,
        private DomainPublicationStateRepository $stateRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
        private DomainBindingTransitionGuardInterface $transitionGuard,
    ) {
    }

    public function prepareRoutingIntent(DomainBinding $binding, string $targetHost, string $targetPath = '/'): DomainRoutingIntent
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::PreparePublication);
        $this->ownershipGuardService->assertRoutingTargetIsAllowed($targetHost, $targetPath);

        $target = $this->targetRepository->findOneBy(['binding' => $binding]) ?? new DomainRoutingTarget($binding, $targetHost, $targetPath);
        $target->retarget($targetHost, $targetPath);
        $state = $this->stateRepository->findOneBy(['binding' => $binding]) ?? new DomainPublicationState($binding);
        $state->markReady();
        $binding->declaration()?->markReady();

        $this->entityManager->persist($target);
        $this->entityManager->persist($state);
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_publication_ready', $binding->ownerId(), [
            'target_host' => $targetHost,
            'target_path' => $targetPath,
        ]));
        $this->entityManager->flush();

        return new DomainRoutingIntent($binding->domainName(), $binding->ownerId(), $binding->surfaceType(), $binding->surfaceKey(), $targetHost, $targetPath);
    }

    public function markPublished(DomainBinding $binding): DomainPublicationSnapshot
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::MarkPublished);

        $state = $this->stateRepository->findOneBy(['binding' => $binding]);
        if (!$state instanceof DomainPublicationState || DomainPublicationStatus::Ready !== $state->status()) {
            throw DomainInvalidStateException::create('Only ready domain publications can be marked as published.');
        }

        $target = $this->targetRepository->findOneBy(['binding' => $binding]);
        if (!$target instanceof DomainRoutingTarget) {
            throw DomainInvalidStateException::create('Domain publication target is missing.');
        }

        $state->markPublished();
        $binding->declaration()?->markPublished();
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_publication_published', $binding->ownerId(), [
            'target_host' => $target->targetHost(),
            'target_path' => $target->targetPath(),
        ]));
        $this->entityManager->flush();

        return new DomainPublicationSnapshot($binding->domainName(), $binding->ownerId(), $state->status(), $target->targetHost(), $target->targetPath());
    }

    public function markWithdrawn(DomainBinding $binding): DomainPublicationSnapshot
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::WithdrawPublication);

        $state = $this->stateRepository->findOneBy(['binding' => $binding]) ?? new DomainPublicationState($binding);
        $target = $this->targetRepository->findOneBy(['binding' => $binding]);

        $state->markWithdrawn();
        $binding->declaration()?->suspend();
        $this->entityManager->persist($state);
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_publication_withdrawn', $binding->ownerId()));
        $this->entityManager->flush();

        return new DomainPublicationSnapshot(
            $binding->domainName(),
            $binding->ownerId(),
            $state->status(),
            $target instanceof DomainRoutingTarget ? $target->targetHost() : null,
            $target instanceof DomainRoutingTarget ? $target->targetPath() : null,
        );
    }
}
