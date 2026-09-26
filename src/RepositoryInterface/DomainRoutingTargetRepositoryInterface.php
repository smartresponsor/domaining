<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;

interface DomainRoutingTargetRepositoryInterface
{
    public function findOneForBinding(DomainBindingEntity $binding): ?DomainRoutingTargetEntity;
}
