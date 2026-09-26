<?php

declare(strict_types=1);

namespace App\Domaining\Service\Declaration;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainDeclarationEntity;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Repository\DomainDeclarationRepository;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Declaration\DomainDeclarationServiceInterface;

final readonly class DomainDeclarationService implements DomainDeclarationServiceInterface
{
    public function __construct(
        private DomainPersistenceRepository $persistenceRepository,
        private DomainDeclarationRepository $declarationRepository,
    ) {
    }

    public function declare(
        string $applicationKey,
        string $brandKey,
        string $environment,
        string $domainName,
        DomainApplicationRole $role = DomainApplicationRole::Primary,
    ): DomainDeclarationEntity {
        $existing = $this->declarationRepository->findOneByDomainAndEnvironment($domainName, $environment);
        if ($existing instanceof DomainDeclarationEntity) {
            if ($existing->applicationKey() !== trim($applicationKey)) {
                throw DomainConflictException::create(sprintf('Domain "%s" is already declared for application "%s" in environment "%s".', $existing->domainName(), $existing->applicationKey(), $existing->environment()));
            }

            $existing->redeclare($brandKey, $role);
            $this->persistenceRepository->flush();

            return $existing;
        }

        $declaration = new DomainDeclarationEntity($applicationKey, $brandKey, $environment, $domainName, $role);
        $this->persistenceRepository->persist($declaration);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($declaration->domainName(), 'domain_declared', $declaration->applicationKey(), [
            'application_key' => $declaration->applicationKey(),
            'brand_key' => $declaration->brandKey(),
            'environment' => $declaration->environment(),
            'role' => $declaration->role()->value,
        ]));
        $this->persistenceRepository->flush();

        return $declaration;
    }
}
