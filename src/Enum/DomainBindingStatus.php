<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainBindingStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Active = 'active';
    case Suspended = 'suspended';
    case Removed = 'removed';
    case Failed = 'failed';
}
