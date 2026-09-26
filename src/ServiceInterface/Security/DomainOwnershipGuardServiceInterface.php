<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Security;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;

interface DomainOwnershipGuardServiceInterface
{
    public function assertClaimCanBeCreated(DomainClaimEntity $claim): void;

    public function assertBindingCanBeActivated(DomainBindingEntity $binding): void;

    public function assertRoutingTargetIsAllowed(string $targetHost, string $targetPath): void;
}
