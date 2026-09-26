<?php

declare(strict_types=1);

namespace App\Domaining\Service\Publication;

use App\Domaining\DTO\DomainPublicationSnapshotDTO;
use App\Domaining\DTO\DomainRoutingIntentDTO;
use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Publication\DomainPublicationServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\ServiceInterface\State\DomainBindingTransitionGuardInterface;

final readonly class DomainPublicationService implements DomainPublicationServiceInterface
{
    public function __construct(
        private DomainPersistenceRepository $persistenceRepository,
        private DomainRoutingTargetRepository $targetRepository,
        private DomainPublicationStateRepository $stateRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
        private DomainBindingTransitionGuardInterface $transitionGuard,
    ) {
    }

    public function prepareRoutingIntent(DomainBindingEntity $binding, string $targetHost, string $targetPath = '/'): DomainRoutingIntentDTO
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::PreparePublication);
        $this->ownershipGuardService->assertRoutingTargetIsAllowed($targetHost, $targetPath);

        $target = $this->targetRepository->findOneBy(['binding' => $binding]) ?? new DomainRoutingTargetEntity($binding, $targetHost, $targetPath);
        $target->retarget($targetHost, $targetPath);
        $state = $this->stateRepository->findOneBy(['binding' => $binding]) ?? new DomainPublicationStateEntity($binding);
        $state->markReady();
        $binding->declaration()?->markReady();

        $this->persistenceRepository->persist($target);
        $this->persistenceRepository->persist($state);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_publication_ready', $binding->ownerId(), [
            'target_host' => $targetHost,
            'target_path' => $targetPath,
        ]));
        $this->persistenceRepository->flush();

        return new DomainRoutingIntentDTO($binding->domainName(), $binding->ownerId(), $binding->surfaceType(), $binding->surfaceKey(), $targetHost, $targetPath);
    }

    public function markPublished(DomainBindingEntity $binding): DomainPublicationSnapshotDTO
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::MarkPublished);

        $state = $this->stateRepository->findOneBy(['binding' => $binding]);
        if (!$state instanceof DomainPublicationStateEntity || DomainPublicationStatus::Ready !== $state->status()) {
            throw DomainInvalidStateException::create('Only ready domain publications can be marked as published.');
        }

        $target = $this->targetRepository->findOneBy(['binding' => $binding]);
        if (!$target instanceof DomainRoutingTargetEntity) {
            throw DomainInvalidStateException::create('Domain publication target is missing.');
        }

        $state->markPublished();
        $binding->declaration()?->markPublished();
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_publication_published', $binding->ownerId(), [
            'target_host' => $target->targetHost(),
            'target_path' => $target->targetPath(),
        ]));
        $this->persistenceRepository->flush();

        return new DomainPublicationSnapshotDTO($binding->domainName(), $binding->ownerId(), $state->status(), $target->targetHost(), $target->targetPath());
    }

    public function markWithdrawn(DomainBindingEntity $binding): DomainPublicationSnapshotDTO
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::WithdrawPublication);

        $state = $this->stateRepository->findOneBy(['binding' => $binding]) ?? new DomainPublicationStateEntity($binding);
        $target = $this->targetRepository->findOneBy(['binding' => $binding]);

        $state->markWithdrawn();
        $binding->declaration()?->suspend();
        $this->persistenceRepository->persist($state);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_publication_withdrawn', $binding->ownerId()));
        $this->persistenceRepository->flush();

        return new DomainPublicationSnapshotDTO(
            $binding->domainName(),
            $binding->ownerId(),
            $state->status(),
            $target instanceof DomainRoutingTargetEntity ? $target->targetHost() : null,
            $target instanceof DomainRoutingTargetEntity ? $target->targetPath() : null,
        );
    }
}
