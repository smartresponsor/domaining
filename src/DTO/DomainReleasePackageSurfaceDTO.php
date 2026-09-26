<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

/**
 * Machine-readable release surface entry for Domaining RC packaging.
 */
final readonly class DomainReleasePackageSurfaceDTO
{
    /**
     * @param list<string>         $endpoint
     * @param list<string>         $command
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $nameEntity,
        public string $schemaVersion,
        public string $purpose,
        public array $endpoint = [],
        public array $command = [],
        public array $metadata = [],
    ) {
    }

    /** @return array{nameEntity: string, schemaVersion: string, purpose: string, endpoint: list<string>, command: list<string>, metadata: array<string, mixed>} */
    public function toArray(): array
    {
        return [
            'nameEntity' => $this->nameEntity,
            'schemaVersion' => $this->schemaVersion,
            'purpose' => $this->purpose,
            'endpoint' => $this->endpoint,
            'command' => $this->command,
            'metadata' => $this->metadata,
        ];
    }
}
