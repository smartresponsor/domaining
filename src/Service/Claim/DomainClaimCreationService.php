<?php

declare(strict_types=1);

namespace App\Domaining\Service\Claim;

use App\Domaining\Dto\DomainClaimRequest;
use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Entity\DomainDeclaration;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainClaimRepository;
use App\Domaining\ServiceInterface\Claim\DomainClaimCreationServiceInterface;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;
use App\Domaining\Value\DomainName;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainClaimCreationService implements DomainClaimCreationServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainClaimRepository $claimRepository,
        private DomainOwnershipGuardServiceInterface $ownershipGuardService,
    ) {
    }

    public function createClaim(DomainClaimRequest $request): DomainClaim
    {
        $domainName = (string) new DomainName($request->domainName);
        $existing = $this->claimRepository->findOneByDomainAndOwner($domainName, $request->ownerId);
        if ($existing instanceof DomainClaim) {
            return $existing;
        }

        $claim = new DomainClaim($domainName, $request->ownerId, $request->surfaceType, $request->surfaceKey);
        $this->ownershipGuardService->assertClaimCanBeCreated($claim);

        $this->entityManager->persist($claim);
        $this->entityManager->persist(new DomainAuditRecord($domainName, 'domain_claim_created', $request->ownerId));
        $this->entityManager->flush();

        return $claim;
    }

    public function createForDeclaration(DomainDeclaration $declaration, string $ownerId): DomainClaim
    {
        if (DomainDeclarationStatus::Declared !== $declaration->status()) {
            throw new \DomainException(sprintf(
                'Domain declaration "%s" cannot start an ownership claim from status "%s".',
                $declaration->domainName(),
                $declaration->status()->value,
            ));
        }

        $ownerId = trim($ownerId);
        if ('' === $ownerId) {
            throw new \InvalidArgumentException('Owner id must not be empty.');
        }

        $existing = $this->claimRepository->findOneByDomainAndOwner($declaration->domainName(), $ownerId);
        if ($existing instanceof DomainClaim) {
            throw new \DomainException(sprintf(
                'Domain "%s" already has an ownership claim for owner "%s".',
                $declaration->domainName(),
                $ownerId,
            ));
        }

        $claim = new DomainClaim(
            $declaration->domainName(),
            $ownerId,
            DomainSurfaceType::Application,
            $declaration->applicationKey(),
            $declaration,
        );
        $this->ownershipGuardService->assertClaimCanBeCreated($claim);
        $declaration->markClaimPending();

        $this->entityManager->persist($claim);
        $this->entityManager->persist(new DomainAuditRecord(
            $declaration->domainName(),
            'domain_claim_created_from_declaration',
            $ownerId,
            [
                'application_key' => $declaration->applicationKey(),
                'environment' => $declaration->environment(),
            ],
        ));
        $this->entityManager->flush();

        return $claim;
    }
}
