<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Manifest;

use App\Domaining\Dto\DomainReleaseManifest;

interface DomainReleaseManifestServiceInterface
{
    public function buildManifest(): DomainReleaseManifest;
}
