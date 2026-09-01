<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use App\Domaining\Enum\DomainDnsProviderHint;

final readonly class DomainDnsInstructionSet
{
    /**
     * @param list<DomainDnsRecordInstruction> $records
     */
    public function __construct(
        public string $domainName,
        public DomainDnsProviderHint $providerHint,
        public array $records,
        public string $note,
    ) {
    }

    /**
     * @return array{domainName: string, providerHint: string, records: list<array{recordType: string, recordName: string, recordValue: string, purpose: string, required: bool}>, note: string}
     */
    public function toArray(): array
    {
        return [
            'domainName' => $this->domainName,
            'providerHint' => $this->providerHint->value,
            'records' => array_map(static fn (DomainDnsRecordInstruction $record): array => $record->toArray(), $this->records),
            'note' => $this->note,
        ];
    }
}
