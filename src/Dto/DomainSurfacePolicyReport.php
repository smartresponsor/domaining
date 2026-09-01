<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use App\Domaining\Enum\DomainSurfaceType;
use DateTimeImmutable;

final readonly class DomainSurfacePolicyReport
{
    /**
     * @param list<DomainSurfacePolicyIssue> $issues
     * @param array<string, int> $severityCount
     */
    public function __construct(
        public DateTimeImmutable $generatedAt,
        public bool $pass,
        public string $ownerId,
        public ?DomainSurfaceType $surfaceType,
        public ?string $surfaceKey,
        public int $liveBindingCount,
        public int $activeBindingCount,
        public int $suspendedBindingCount,
        public array $severityCount,
        public array $issues,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'pass' => $this->pass,
            'ownerId' => $this->ownerId,
            'surfaceType' => $this->surfaceType?->value,
            'surfaceKey' => $this->surfaceKey,
            'liveBindingCount' => $this->liveBindingCount,
            'activeBindingCount' => $this->activeBindingCount,
            'suspendedBindingCount' => $this->suspendedBindingCount,
            'severityCount' => $this->severityCount,
            'issueCount' => count($this->issues),
            'issues' => array_map(static fn (DomainSurfacePolicyIssue $issue): array => $issue->toArray(), $this->issues),
        ];
    }
}
