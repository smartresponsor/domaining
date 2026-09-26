<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainDiagnosticReportDTO
{
    /**
     * @param list<DomainDiagnosticIssueDTO> $issues
     * @param array<string, int>             $severityCount
     */
    public function __construct(
        public \DateTimeImmutable $generatedAt,
        public bool $pass,
        public array $severityCount,
        public array $issues,
    ) {
    }

    /**
     * @return array{generatedAt: string, pass: bool, severityCount: array<string, int>, issueCount: int, issues: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'pass' => $this->pass,
            'severityCount' => $this->severityCount,
            'issueCount' => count($this->issues),
            'issues' => array_map(static fn (DomainDiagnosticIssueDTO $issue): array => $issue->toArray(), $this->issues),
        ];
    }
}
