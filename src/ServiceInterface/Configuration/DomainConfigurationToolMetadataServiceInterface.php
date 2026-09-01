<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Configuration;

use App\Domaining\Dto\DomainConfigurationToolDescriptor;

interface DomainConfigurationToolMetadataServiceInterface
{
    public function descriptor(): DomainConfigurationToolDescriptor;
}
