<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Diagnostic;

use App\Domaining\Dto\DomainDiagnosticReport;

interface DomainDiagnosticServiceInterface
{
    public function buildReport(): DomainDiagnosticReport;
}
