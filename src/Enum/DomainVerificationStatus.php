<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainVerificationStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Failed = 'failed';
    case Expired = 'expired';
}
