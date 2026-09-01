<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\State;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Enum\DomainLifecycleTransition;

interface DomainBindingTransitionGuardInterface
{
    public function assertAllowed(DomainBinding $binding, DomainLifecycleTransition $transition): void;

    public function isAllowed(DomainBinding $binding, DomainLifecycleTransition $transition): bool;
}
