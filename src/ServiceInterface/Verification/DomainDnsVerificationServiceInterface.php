<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\Dto\DomainVerificationResult;
use App\Domaining\Entity\DomainVerificationChallenge;

interface DomainDnsVerificationServiceInterface
{
    public function verify(DomainVerificationChallenge $challenge): DomainVerificationResult;
    public function recheck(DomainVerificationChallenge $challenge): DomainVerificationResult;
}
