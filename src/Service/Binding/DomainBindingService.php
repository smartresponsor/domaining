<?php

declare(strict_types=1);

namespace App\Domaining\Service\Binding;

use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\ServiceInterface\Binding\DomainBindingServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\ServiceInterface\State\DomainBindingTransitionGuardInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainBindingService implements DomainBindingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainBindingRepository $bindingRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
        private DomainBindingTransitionGuardInterface $transitionGuard,
    ) {
    }

    public function createFromVerifiedClaim(DomainClaim $claim): DomainBinding
    {
        if (DomainClaimStatus::Verified !== $claim->status()) {
            throw DomainInvalidStateException::create('Only verified claims can become domain bindings.');
        }
        if (null !== $claim->declaration() && DomainDeclarationStatus::Verified !== $claim->declaration()->status()) {
            throw DomainInvalidStateException::create('Application domain declarations must be verified before binding.');
        }

        $existing = $this->bindingRepository->findLiveByDomainName($claim->domainName());
        if ($existing instanceof DomainBinding && $existing->ownerId() !== $claim->ownerId()) {
            throw DomainConflictException::create(sprintf('Domain "%s" is already bound to another owner.', $claim->domainName()));
        }

        if ($existing instanceof DomainBinding) {
            $existing->markVerifiedNow();
            $this->entityManager->flush();

            return $existing;
        }

        $binding = new DomainBinding($claim->domainName(), $claim->ownerId(), $claim->surfaceType(), $claim->surfaceKey(), $claim->declaration());
        $binding->markVerifiedNow();
        $this->entityManager->persist($binding);
        $this->entityManager->persist(new DomainAuditRecord($claim->domainName(), 'domain_binding_created', $claim->ownerId()));
        $this->entityManager->flush();

        return $binding;
    }

    public function activate(DomainBinding $binding): DomainBinding
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::ActivateBinding);
        $this->ownershipGuardService->assertBindingCanBeActivated($binding);

        $binding->activate();
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_binding_activated', $binding->ownerId()));
        $this->entityManager->flush();

        return $binding;
    }

    public function suspend(DomainBinding $binding): DomainBinding
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::SuspendBinding);

        $binding->suspend();
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_binding_suspended', $binding->ownerId()));
        $this->entityManager->flush();

        return $binding;
    }

    public function remove(DomainBinding $binding): DomainBinding
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::RemoveBinding);

        $binding->remove();
        $this->entityManager->persist(new DomainAuditRecord($binding->domainName(), 'domain_binding_removed', $binding->ownerId()));
        $this->entityManager->flush();

        return $binding;
    }
}
