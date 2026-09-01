<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Security;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainClaim;

interface DomainOwnershipGuardServiceInterface
{
    public function assertClaimCanBeCreated(DomainClaim $claim): void;

    public function assertBindingCanBeActivated(DomainBinding $binding): void;

    public function assertRoutingTargetIsAllowed(string $targetHost, string $targetPath): void;
}
