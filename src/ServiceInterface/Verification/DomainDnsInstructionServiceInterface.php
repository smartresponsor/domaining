<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Verification;

use App\Domaining\DTO\DomainDnsInstructionSetDTO;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainDnsProviderHint;

interface DomainDnsInstructionServiceInterface
{
    public function buildInstructionSet(DomainVerificationChallengeEntity $challenge, DomainDnsProviderHint $providerHint = DomainDnsProviderHint::Unknown): DomainDnsInstructionSetDTO;
}
