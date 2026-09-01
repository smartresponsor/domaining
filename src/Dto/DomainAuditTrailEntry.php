<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use DateTimeImmutable;

final readonly class DomainAuditTrailEntry
{
    /** @param array<string, mixed> $context */
    public function __construct(
        public string $id,
        public string $domainName,
        public string $action,
        public ?string $actorId,
        public array $context,
        public DateTimeImmutable $createdAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_name' => $this->domainName,
            'action' => $this->action,
            'actor_id' => $this->actorId,
            'context' => $this->context,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}
