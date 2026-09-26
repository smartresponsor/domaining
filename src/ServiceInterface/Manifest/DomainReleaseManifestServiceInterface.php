<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Manifest;

use App\Domaining\DTO\DomainReleaseManifestDTO;

interface DomainReleaseManifestServiceInterface
{
    public function buildManifest(): DomainReleaseManifestDTO;
}
