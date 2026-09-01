<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainDeclarationStatus: string
{
    case Declared = 'declared';
    case ClaimPending = 'claim_pending';
    case Verified = 'verified';
    case Ready = 'ready';
    case Published = 'published';
    case Suspended = 'suspended';
    case Withdrawn = 'withdrawn';
}
