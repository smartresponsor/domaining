<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\Dto\DomainDnsInstructionSet;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainDnsProviderHint;

interface DomainDnsInstructionServiceInterface
{
    public function buildInstructionSet(DomainVerificationChallenge $challenge, DomainDnsProviderHint $providerHint = DomainDnsProviderHint::Unknown): DomainDnsInstructionSet;
}
