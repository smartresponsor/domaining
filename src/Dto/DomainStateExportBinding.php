<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainStateExportBinding
{
    public function __construct(
        public string $domainName,
        public string $ownerId,
        public string $surfaceType,
        public string $surfaceKey,
        public string $bindingStatus,
        public ?string $publicationStatus,
        public ?string $targetHost,
        public ?string $targetPath,
        public ?string $lastVerifiedAt,
        public string $reviewState,
    ) {
    }

    /**
     * @return array{domainName: string, ownerId: string, surfaceType: string, surfaceKey: string, bindingStatus: string, publicationStatus: string|null, targetHost: string|null, targetPath: string|null, lastVerifiedAt: string|null, reviewState: string}
     */
    public function toArray(): array
    {
        return [
            'domainName' => $this->domainName,
            'ownerId' => $this->ownerId,
            'surfaceType' => $this->surfaceType,
            'surfaceKey' => $this->surfaceKey,
            'bindingStatus' => $this->bindingStatus,
            'publicationStatus' => $this->publicationStatus,
            'targetHost' => $this->targetHost,
            'targetPath' => $this->targetPath,
            'lastVerifiedAt' => $this->lastVerifiedAt,
            'reviewState' => $this->reviewState,
        ];
    }
}
