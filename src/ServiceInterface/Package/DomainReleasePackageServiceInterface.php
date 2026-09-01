<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Package;

use App\Domaining\Dto\DomainReleasePackageReport;

interface DomainReleasePackageServiceInterface
{
    public function buildPackage(): DomainReleasePackageReport;
}
