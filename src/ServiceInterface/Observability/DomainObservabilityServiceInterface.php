<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Observability;

use App\Domaining\DTO\DomainReadinessReportDTO;

interface DomainObservabilityServiceInterface
{
    public function readinessReport(): DomainReadinessReportDTO;

    /**
     * @return array<string, mixed>
     */
    public function metricSnapshot(): array;
}
