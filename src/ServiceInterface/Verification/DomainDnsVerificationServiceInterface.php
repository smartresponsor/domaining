<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\DTO\DomainVerificationResultDTO;
use App\Domaining\Entity\DomainVerificationChallengeEntity;

interface DomainDnsVerificationServiceInterface
{
    public function verify(DomainVerificationChallengeEntity $challenge): DomainVerificationResultDTO;

    public function recheck(DomainVerificationChallengeEntity $challenge): DomainVerificationResultDTO;
}
