<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainClaimStatus: string
{
    case Pending = 'pending';
    case ChallengeIssued = 'challenge_issued';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
