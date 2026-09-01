<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use DateTimeImmutable;

/**
 * Final machine-readable RC review report for Domaining.
 *
 * This report composes the component-owned release surfaces without coupling
 * Domaining to provider-specific registrar, DNS, TLS, or runtime mutation APIs.
 */
final readonly class DomainReleaseReviewReport
{
    /**
     * @param list<DomainReleaseReviewIssue> $issue
     * @param array<string, mixed> $surface
     */
    public function __construct(
        public string $schemaVersion,
        public DateTimeImmutable $generatedAt,
        public bool $releaseCandidateReady,
        public array $issue,
        public array $surface,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'releaseCandidateReady' => $this->releaseCandidateReady,
            'issueCount' => count($this->issue),
            'issue' => array_map(static fn (DomainReleaseReviewIssue $issue): array => $issue->toArray(), $this->issue),
            'surface' => $this->surface,
        ];
    }
}
