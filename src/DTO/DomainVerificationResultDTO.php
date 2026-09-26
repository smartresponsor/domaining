<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

use App\Domaining\Enum\DomainVerificationStatus;

final readonly class DomainVerificationResultDTO
{
    /** @param array<string, mixed> $observedRecord */
    public function __construct(
        public DomainVerificationStatus $status,
        public string $message,
        public array $observedRecord = [],
    ) {
    }

    public function passed(): bool
    {
        return DomainVerificationStatus::Passed === $this->status;
    }
}
