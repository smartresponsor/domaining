<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainRuntimeHandoffItemDTO
{
    public function __construct(
        public string $domainName,
        public string $ownerId,
        public string $surfaceType,
        public string $surfaceKey,
        public string $targetHost,
        public string $targetPath,
        public string $publicationStatus,
        public string $bindingStatus,
        public string $action,
    ) {
    }

    /**
     * @return array{domainName: string, ownerId: string, surfaceType: string, surfaceKey: string, targetHost: string, targetPath: string, publicationStatus: string, bindingStatus: string, action: string}
     */
    public function toArray(): array
    {
        return [
            'domainName' => $this->domainName,
            'ownerId' => $this->ownerId,
            'surfaceType' => $this->surfaceType,
            'surfaceKey' => $this->surfaceKey,
            'targetHost' => $this->targetHost,
            'targetPath' => $this->targetPath,
            'publicationStatus' => $this->publicationStatus,
            'bindingStatus' => $this->bindingStatus,
            'action' => $this->action,
        ];
    }
}
