<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\Dto\DomainDnsInstructionSet;
use App\Domaining\Dto\DomainDnsRecordInstruction;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainDnsProviderHint;
use App\Domaining\ServiceInterface\Verification\DomainDnsInstructionServiceInterface;

final readonly class DomainDnsInstructionService implements DomainDnsInstructionServiceInterface
{
    public function buildInstructionSet(DomainVerificationChallenge $challenge, DomainDnsProviderHint $providerHint = DomainDnsProviderHint::Unknown): DomainDnsInstructionSet
    {
        return new DomainDnsInstructionSet(
            $challenge->claim()->domainName(),
            $providerHint,
            [
                new DomainDnsRecordInstruction(
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
