<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Observability;

use App\Domaining\Dto\DomainReadinessReport;

interface DomainObservabilityServiceInterface
{
    public function readinessReport(): DomainReadinessReport;

    /**
     * @return array<string, mixed>
     */
    public function metricSnapshot(): array;
}
