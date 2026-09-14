<?php

declare(strict_types=1);

namespace App\Domaining\Repository;

use App\Domaining\Entity\DomainDeclaration;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\RepositoryInterface\DomainDeclarationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<DomainDeclaration> */
final class DomainDeclarationRepository extends ServiceEntityRepository implements DomainDeclarationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainDeclaration::class);
    }

    public function findOneById(string $id): ?DomainDeclaration
    {
        $declaration = $this->find($id);

        return $declaration instanceof DomainDeclaration ? $declaration : null;
    }

    public function findOneByDomainAndEnvironment(string $domainName, string $environment): ?DomainDeclaration
    {
        $declaration = $this->findOneBy([
            'domainName' => strtolower(rtrim(trim($domainName), '.')),
            'environment' => trim($environment),
        ]);

        return $declaration instanceof DomainDeclaration ? $declaration : null;
    }

    public function findPrimaryByApplication(string $applicationKey, string $environment): ?DomainDeclaration
    {
        $declaration = $this->findOneBy([
            'applicationKey' => trim($applicationKey),
            'environment' => trim($environment),
            'role' => DomainApplicationRole::Primary,
        ]);

        return $declaration instanceof DomainDeclaration ? $declaration : null;
    }

    /**
     * @return list<DomainDeclaration>
     */
    public function findByApplication(string $applicationKey, string $environment): array
    {
        return $this->findBy([
            'applicationKey' => trim($applicationKey),
            'environment' => trim($environment),
        ]);
    }
}
