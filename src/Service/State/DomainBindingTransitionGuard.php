<?php

declare(strict_types=1);

namespace App\Domaining\Service\State;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\ServiceInterface\State\DomainBindingTransitionGuardInterface;

final readonly class DomainBindingTransitionGuard implements DomainBindingTransitionGuardInterface
{
    public function assertAllowed(DomainBindingEntity $binding, DomainLifecycleTransition $transition): void
    {
        if (!$this->isAllowed($binding, $transition)) {
            throw DomainInvalidStateException::create(sprintf('Transition "%s" is not allowed for domain binding status "%s".', $transition->value, $binding->status()->value));
        }
    }

    public function isAllowed(DomainBindingEntity $binding, DomainLifecycleTransition $transition): bool
    {
        return match ($transition) {
            DomainLifecycleTransition::ActivateBinding => in_array($binding->status(), [DomainBindingStatus::Verified, DomainBindingStatus::Suspended], true),
            DomainLifecycleTransition::SuspendBinding => in_array($binding->status(), [DomainBindingStatus::Verified, DomainBindingStatus::Active], true),
            DomainLifecycleTransition::RemoveBinding => in_array($binding->status(), [DomainBindingStatus::Verified, DomainBindingStatus::Suspended], true),
            DomainLifecycleTransition::PreparePublication => in_array($binding->status(), [DomainBindingStatus::Verified, DomainBindingStatus::Active], true),
            DomainLifecycleTransition::MarkPublished => DomainBindingStatus::Active === $binding->status(),
            DomainLifecycleTransition::WithdrawPublication => DomainBindingStatus::Suspended === $binding->status() || DomainBindingStatus::Removed === $binding->status(),
        };
    }
}
