<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Binding;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainClaim;

interface DomainBindingServiceInterface
{
    public function createFromVerifiedClaim(DomainClaim $claim): DomainBinding;
    public function activate(DomainBinding $binding): DomainBinding;
    public function suspend(DomainBinding $binding): DomainBinding;
    public function remove(DomainBinding $binding): DomainBinding;
}
