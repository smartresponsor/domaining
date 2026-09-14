<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainReleaseManifestContract
{
    /**
     * @param list<string> $endpoint
     * @param list<string> $command
     */
    public function __construct(
        public string $nameEntity,
        public string $version,
        public string $owner,
        public array $endpoint,
        public array $command,
    ) {
    }

    /**
     * @return array{nameEntity: string, version: string, owner: string, endpoint: list<string>, command: list<string>}
     */
    public function toArray(): array
    {
        return [
            'nameEntity' => $this->nameEntity,
            'version' => $this->version,
            'owner' => $this->owner,
            'endpoint' => $this->endpoint,
            'command' => $this->command,
        ];
    }
}

