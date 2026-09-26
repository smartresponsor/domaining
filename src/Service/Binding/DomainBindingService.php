<?php

declare(strict_types=1);

namespace App\Domaining\Service\Binding;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Binding\DomainBindingServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\ServiceInterface\State\DomainBindingTransitionGuardInterface;

final readonly class DomainBindingService implements DomainBindingServiceInterface
{
    public function __construct(
        private DomainPersistenceRepository $persistenceRepository,
        private DomainBindingRepository $bindingRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
        private DomainBindingTransitionGuardInterface $transitionGuard,
    ) {
    }

    public function createFromVerifiedClaim(DomainClaimEntity $claim): DomainBindingEntity
    {
        if (DomainClaimStatus::Verified !== $claim->status()) {
            throw DomainInvalidStateException::create('Only verified claims can become domain bindings.');
        }
        if (null !== $claim->declaration() && DomainDeclarationStatus::Verified !== $claim->declaration()->status()) {
            throw DomainInvalidStateException::create('Application domain declarations must be verified before binding.');
        }

        $existing = $this->bindingRepository->findLiveByDomainName($claim->domainName());
        if ($existing instanceof DomainBindingEntity && $existing->ownerId() !== $claim->ownerId()) {
            throw DomainConflictException::create(sprintf('Domain "%s" is already bound to another owner.', $claim->domainName()));
        }

        if ($existing instanceof DomainBindingEntity) {
            $existing->markVerifiedNow();
            $this->persistenceRepository->flush();

            return $existing;
        }

        $binding = new DomainBindingEntity($claim->domainName(), $claim->ownerId(), $claim->surfaceType(), $claim->surfaceKey(), $claim->declaration());
        $binding->markVerifiedNow();
        $this->persistenceRepository->persist($binding);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($claim->domainName(), 'domain_binding_created', $claim->ownerId()));
        $this->persistenceRepository->flush();

        return $binding;
    }

    public function activate(DomainBindingEntity $binding): DomainBindingEntity
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::ActivateBinding);
        $this->ownershipGuardService->assertBindingCanBeActivated($binding);

        $binding->activate();
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_binding_activated', $binding->ownerId()));
        $this->persistenceRepository->flush();

        return $binding;
    }

    public function suspend(DomainBindingEntity $binding): DomainBindingEntity
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::SuspendBinding);

        $binding->suspend();
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_binding_suspended', $binding->ownerId()));
        $this->persistenceRepository->flush();

        return $binding;
    }

    public function remove(DomainBindingEntity $binding): DomainBindingEntity
    {
        $this->transitionGuard->assertAllowed($binding, DomainLifecycleTransition::RemoveBinding);

        $binding->remove();
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($binding->domainName(), 'domain_binding_removed', $binding->ownerId()));
        $this->persistenceRepository->flush();

        return $binding;
    }
}
