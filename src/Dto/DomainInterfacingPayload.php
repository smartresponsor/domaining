<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainInterfacingPayload
{
    /**
     * @param array<string, mixed> $locations
     * @param array<string, mixed> $binding
     * @param array<string, mixed> $publication
     * @param array<string, mixed> $slotContract
     */
    public function __construct(
        public string $schemaVersion,
        public string $surface,
        public string $domainName,
        public string $ownerId,
        public string $surfaceType,
        public string $surfaceKey,
        public array $locations,
        public array $binding = [],
        public array $publication = [],
        public array $slotContract = [],
    ) {
    }

    /**
     * @return array{
     *     schemaVersion: string,
     *     surface: string,
     *     domainName: string,
     *     ownerId: string,
     *     surfaceType: string,
     *     surfaceKey: string,
     *     locations: array<string, mixed>,
     *     binding: array<string, mixed>,
     *     publication: array<string, mixed>,
     *     slotContract: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'surface' => $this->surface,
            'domainName' => $this->domainName,
            'ownerId' => $this->ownerId,
            'surfaceType' => $this->surfaceType,
            'surfaceKey' => $this->surfaceKey,
            'locations' => $this->locations,
            'binding' => $this->binding,
            'publication' => $this->publication,
            'slotContract' => $this->slotContract,
        ];
    }
}
