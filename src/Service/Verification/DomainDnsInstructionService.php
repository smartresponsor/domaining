<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\DTO\DomainDnsInstructionSetDTO;
use App\Domaining\DTO\DomainDnsRecordInstructionDTO;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainDnsProviderHint;
use App\Domaining\ServiceInterface\Verification\DomainDnsInstructionServiceInterface;

final readonly class DomainDnsInstructionService implements DomainDnsInstructionServiceInterface
{
    public function buildInstructionSet(DomainVerificationChallengeEntity $challenge, DomainDnsProviderHint $providerHint = DomainDnsProviderHint::Unknown): DomainDnsInstructionSetDTO
    {
        return new DomainDnsInstructionSetDTO(
            $challenge->claim()->domainName(),
            $providerHint,
            [
                new DomainDnsRecordInstructionDTO(
                    $challenge->recordType(),
                    $challenge->recordName(),
                    $challenge->recordValue(),
                    'Proves that the current claimant controls DNS for this already registered domain.',
                    true,
                ),
            ],
            'Create the DNS record at your current registrar or DNS provider. Smart Responsor does not sell, transfer, or host registered domains as a registrar.',
        );
    }
}
