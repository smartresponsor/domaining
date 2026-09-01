<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainDeclaration;

interface DomainDeclarationRepositoryInterface
{
    public function findPrimaryByApplication(string $applicationKey, string $environment): ?DomainDeclaration;
}
