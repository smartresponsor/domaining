<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

use App\Domaining\Enum\DomainRecordType;

final readonly class DomainDnsRecordInstructionDTO
{
    public function __construct(
        public DomainRecordType $recordType,
        public string $recordName,
        public string $recordValue,
        public string $purpose,
        public bool $required,
    ) {
    }

    /**
     * @return array{recordType: string, recordName: string, recordValue: string, purpose: string, required: bool}
     */
    public function toArray(): array
    {
        return [
            'recordType' => $this->recordType->value,
            'recordName' => $this->recordName,
            'recordValue' => $this->recordValue,
            'purpose' => $this->purpose,
            'required' => $this->required,
        ];
    }
}
