<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainDeclaration;

interface DomainBindingRepositoryInterface
{
    public function findOneForDeclaration(DomainDeclaration $declaration): ?DomainBinding;
}
