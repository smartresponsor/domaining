<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainStateExportReportDTO
{
    /**
     * @param list<DomainStateExportBindingDTO> $bindings
     * @param list<string>                      $warnings
     */
    public function __construct(
        public string $schemaVersion,
        public \DateTimeImmutable $generatedAt,
        public array $bindings,
        public array $warnings = [],
    ) {
    }

    /**
     * @return array{schemaVersion: string, generatedAt: string, count: int, warnings: list<string>, bindings: list<array{domainName: string, ownerId: string, surfaceType: string, surfaceKey: string, bindingStatus: string, publicationStatus: string|null, targetHost: string|null, targetPath: string|null, lastVerifiedAt: string|null, reviewState: string}>}
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'count' => count($this->bindings),
            'warnings' => $this->warnings,
            'bindings' => array_map(static fn (DomainStateExportBindingDTO $binding): array => $binding->toArray(), $this->bindings),
        ];
    }
}
