<?php

declare(strict_types=1);

namespace App\Domaining\Service\Declaration;

use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainDeclaration;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Repository\DomainDeclarationRepository;
use App\Domaining\ServiceInterface\Declaration\DomainDeclarationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainDeclarationService implements DomainDeclarationServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainDeclarationRepository $declarationRepository,
    ) {
    }

    public function declare(
        string $applicationKey,
        string $brandKey,
        string $environment,
        string $domainName,
        DomainApplicationRole $role = DomainApplicationRole::Primary,
    ): DomainDeclaration {
        $existing = $this->declarationRepository->findOneByDomainAndEnvironment($domainName, $environment);
        if ($existing instanceof DomainDeclaration) {
            if ($existing->applicationKey() !== trim($applicationKey)) {
                throw DomainConflictException::create(sprintf(
                    'Domain "%s" is already declared for application "%s" in environment "%s".',
                    $existing->domainName(),
                    $existing->applicationKey(),
                    $existing->environment(),
                ));
            }

            $existing->redeclare($brandKey, $role);
            $this->entityManager->flush();

            return $existing;
        }

        $declaration = new DomainDeclaration($applicationKey, $brandKey, $environment, $domainName, $role);
        $this->entityManager->persist($declaration);
        $this->entityManager->persist(new DomainAuditRecord($declaration->domainName(), 'domain_declared', $declaration->applicationKey(), [
            'application_key' => $declaration->applicationKey(),
            'brand_key' => $declaration->brandKey(),
            'environment' => $declaration->environment(),
            'role' => $declaration->role()->value,
        ]));
        $this->entityManager->flush();

        return $declaration;
    }
}
