<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

use App\Domaining\Enum\DomainSurfaceType;

final readonly class DomainRoutingIntentDTO
{
    public function __construct(
        public string $domainName,
        public string $ownerId,
        public DomainSurfaceType $surfaceType,
        public string $surfaceKey,
        public string $targetHost,
        public string $targetPath = '/',
    ) {
    }
}
