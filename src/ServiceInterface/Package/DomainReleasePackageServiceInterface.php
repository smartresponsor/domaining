<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Package;

use App\Domaining\DTO\DomainReleasePackageReportDTO;

interface DomainReleasePackageServiceInterface
{
    public function buildPackage(): DomainReleasePackageReportDTO;
}
