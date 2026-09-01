<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use DateTimeImmutable;

/**
 * Final provider-neutral RC package surface for Domaining.
 *
 * The package report is intended for release review, CI capture, Administering,
 * and runtime-provider handoff preparation. It does not mutate DNS, TLS,
 * registrar state, or edge routing infrastructure.
 */
final readonly class DomainReleasePackageReport
{
    /**
     * @param list<DomainReleasePackageSurface> $surface
     * @param list<string> $requiredCommand
     * @param list<string> $recommendedArtifact
     * @param list<string> $boundary
     * @param array<string, mixed> $snapshot
     */
    public function __construct(
        public string $schemaVersion,
        public DateTimeImmutable $generatedAt,
        public bool $packageReady,
        public string $component,
        public string $package,
        public array $surface,
        public array $requiredCommand,
        public array $recommendedArtifact,
        public array $boundary,
        public array $snapshot,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'packageReady' => $this->packageReady,
            'component' => $this->component,
            'package' => $this->package,
            'surfaceCount' => count($this->surface),
            'surface' => array_map(static fn (DomainReleasePackageSurface $surface): array => $surface->toArray(), $this->surface),
            'requiredCommand' => $this->requiredCommand,
            'recommendedArtifact' => $this->recommendedArtifact,
            'boundary' => $this->boundary,
            'snapshot' => $this->snapshot,
        ];
    }
}
