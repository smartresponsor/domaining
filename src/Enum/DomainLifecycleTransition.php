<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainLifecycleTransition: string
{
    case ActivateBinding = 'activate_binding';
    case SuspendBinding = 'suspend_binding';
    case RemoveBinding = 'remove_binding';
    case PreparePublication = 'prepare_publication';
    case MarkPublished = 'mark_published';
    case WithdrawPublication = 'withdraw_publication';
}
