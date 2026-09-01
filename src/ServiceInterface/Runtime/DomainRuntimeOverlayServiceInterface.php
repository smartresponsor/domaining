<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Runtime;

use App\Domaining\Dto\DomainRuntimeOverlay;

interface DomainRuntimeOverlayServiceInterface
{
    public function forApplication(string $applicationKey, string $environment): DomainRuntimeOverlay;
}
