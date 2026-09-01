<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Export;

use App\Domaining\Dto\DomainStateExportReport;

interface DomainStateExportServiceInterface
{
    public function buildExport(): DomainStateExportReport;
}
