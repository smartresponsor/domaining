<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Export;

use App\Domaining\DTO\DomainStateExportReportDTO;

interface DomainStateExportServiceInterface
{
    public function buildExport(): DomainStateExportReportDTO;
}
