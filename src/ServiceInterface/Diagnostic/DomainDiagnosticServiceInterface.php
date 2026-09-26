<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Diagnostic;

use App\Domaining\DTO\DomainDiagnosticReportDTO;

interface DomainDiagnosticServiceInterface
{
    public function buildReport(): DomainDiagnosticReportDTO;
}
