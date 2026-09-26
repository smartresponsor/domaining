<?php

declare(strict_types=1);

namespace App\Domaining\Service\Claim;

use App\Domaining\DTO\DomainClaimRequestDTO;
use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainDeclarationEntity;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainClaimRepository;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Claim\DomainClaimCreationServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\Value\DomainName;

final readonly class DomainClaimCreationService implements DomainClaimCreationServiceInterface
{
    public function __construct(
        private DomainPersistenceRepository $persistenceRepository,
        private DomainClaimRepository $claimRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
    ) {
    }

    public function createClaim(DomainClaimRequestDTO $request): DomainClaimEntity
    {
        $domainName = (string) new DomainName($request->domainName);
        $existing = $this->claimRepository->findOneByDomainAndOwner($domainName, $request->ownerId);
        if ($existing instanceof DomainClaimEntity) {
            return $existing;
        }

        $claim = new DomainClaimEntity($domainName, $request->ownerId, $request->surfaceType, $request->surfaceKey);
        $this->ownershipGuardService->assertClaimCanBeCreated($claim);

        $this->persistenceRepository->persist($claim);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($domainName, 'domain_claim_created', $request->ownerId));
        $this->persistenceRepository->flush();

        return $claim;
    }

    public function createForDeclaration(DomainDeclarationEntity $declaration, string $ownerId): DomainClaimEntity
    {
        if (DomainDeclarationStatus::Declared !== $declaration->status()) {
            throw new \DomainException(sprintf('Domain declaration "%s" cannot start an ownership claim from status "%s".', $declaration->domainName(), $declaration->status()->value));
        }

        $ownerId = trim($ownerId);
        if ('' === $ownerId) {
            throw new \InvalidArgumentException('Owner id must not be empty.');
        }

        $existing = $this->claimRepository->findOneByDomainAndOwner($declaration->domainName(), $ownerId);
        if ($existing instanceof DomainClaimEntity) {
            throw new \DomainException(sprintf('Domain "%s" already has an ownership claim for owner "%s".', $declaration->domainName(), $ownerId));
        }

        $claim = new DomainClaimEntity(
            $declaration->domainName(),
            $ownerId,
            DomainSurfaceType::Application,
            $declaration->applicationKey(),
            $declaration,
        );
        $this->ownershipGuardService->assertClaimCanBeCreated($claim);
        $declaration->markClaimPending();

        $this->persistenceRepository->persist($claim);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity(
            $declaration->domainName(),
            'domain_claim_created_from_declaration',
            $ownerId,
            [
                'application_key' => $declaration->applicationKey(),
                'environment' => $declaration->environment(),
            ],
        ));
        $this->persistenceRepository->flush();

        return $claim;
    }
}
