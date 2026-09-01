<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Runtime;

use App\Domaining\Dto\DomainRuntimeHandoffReport;

interface DomainRuntimeHandoffServiceInterface
{
    public function buildReport(): DomainRuntimeHandoffReport;
}
