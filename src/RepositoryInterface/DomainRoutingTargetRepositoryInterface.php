<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainRoutingTarget;

interface DomainRoutingTargetRepositoryInterface
{
    public function findOneForBinding(DomainBinding $binding): ?DomainRoutingTarget;
}
