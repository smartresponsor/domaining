<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainDiagnosticIssue
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $severity,
        public string $code,
        public string $message,
        public ?string $domainName = null,
        public ?string $ownerId = null,
        public ?string $surfaceType = null,
        public ?string $surfaceKey = null,
        public array $context = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'code' => $this->code,
            'message' => $this->message,
            'domainName' => $this->domainName,
            'ownerId' => $this->ownerId,
            'surfaceType' => $this->surfaceType,
            'surfaceKey' => $this->surfaceKey,
            'context' => $this->context,
        ];
    }
}
