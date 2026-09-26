<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;

interface DomainVerificationChallengeServiceInterface
{
    public function issueTxtChallenge(DomainClaimEntity $claim): DomainVerificationChallengeEntity;
}
