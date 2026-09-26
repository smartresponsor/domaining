<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Binding;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;

interface DomainBindingServiceInterface
{
    public function createFromVerifiedClaim(DomainClaimEntity $claim): DomainBindingEntity;

    public function activate(DomainBindingEntity $binding): DomainBindingEntity;

    public function suspend(DomainBindingEntity $binding): DomainBindingEntity;

    public function remove(DomainBindingEntity $binding): DomainBindingEntity;
}
