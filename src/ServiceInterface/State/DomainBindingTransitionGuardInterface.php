<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\State;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Enum\DomainLifecycleTransition;

interface DomainBindingTransitionGuardInterface
{
    public function assertAllowed(DomainBindingEntity $binding, DomainLifecycleTransition $transition): void;

    public function isAllowed(DomainBindingEntity $binding, DomainLifecycleTransition $transition): bool;
}
