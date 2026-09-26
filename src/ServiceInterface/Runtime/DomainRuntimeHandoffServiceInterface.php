<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Runtime;

use App\Domaining\DTO\DomainRuntimeHandoffReportDTO;

interface DomainRuntimeHandoffServiceInterface
{
    public function buildReport(): DomainRuntimeHandoffReportDTO;
}
