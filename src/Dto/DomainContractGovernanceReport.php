<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use DateTimeImmutable;

final readonly class DomainContractGovernanceReport
{
    /**
     * @param array<string, string> $contractVersion
     * @param list<string> $endpoint
     * @param list<string> $command
     * @param list<DomainContractGovernanceIssue> $issue
     */
    public function __construct(
        public string $schemaVersion,
        public DateTimeImmutable $generatedAt,
        public bool $ready,
        public array $contractVersion,
        public array $endpoint,
        public array $command,
        public array $issue,
    ) {
    }

    /**
     * @return array{schemaVersion: string, generatedAt: string, ready: bool, contractVersion: array<string, string>, endpoint: list<string>, command: list<string>, issueCount: int, issue: list<array{severity: string, code: string, message: string, context: array<string, mixed>}>}
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'ready' => $this->ready,
            'contractVersion' => $this->contractVersion,
            'endpoint' => $this->endpoint,
            'command' => $this->command,
            'issueCount' => count($this->issue),
            'issue' => array_map(static fn (DomainContractGovernanceIssue $issue): array => $issue->toArray(), $this->issue),
        ];
    }
}
