<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

use App\Domaining\Enum\DomainPublicationStatus;

final readonly class DomainPublicationSnapshotDTO
{
    public function __construct(
        public string $domainName,
        public string $ownerId,
        public DomainPublicationStatus $status,
        public ?string $targetHost,
        public ?string $targetPath,
    ) {
    }

    /**
     * @return array{domainName: string, ownerId: string, status: string, targetHost: ?string, targetPath: ?string}
     */
    public function toArray(): array
    {
        return [
            'domainName' => $this->domainName,
            'ownerId' => $this->ownerId,
            'status' => $this->status->value,
            'targetHost' => $this->targetHost,
            'targetPath' => $this->targetPath,
        ];
    }
}
