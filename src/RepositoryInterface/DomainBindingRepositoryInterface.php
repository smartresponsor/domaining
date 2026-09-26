<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainDeclarationEntity;

interface DomainBindingRepositoryInterface
{
    public function findOneForDeclaration(DomainDeclarationEntity $declaration): ?DomainBindingEntity;
}
