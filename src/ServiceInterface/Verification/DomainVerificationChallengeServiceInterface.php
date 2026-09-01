<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\Entity\DomainClaim;
use App\Domaining\Entity\DomainVerificationChallenge;

interface DomainVerificationChallengeServiceInterface
{
    public function issueTxtChallenge(DomainClaim $claim): DomainVerificationChallenge;
}
