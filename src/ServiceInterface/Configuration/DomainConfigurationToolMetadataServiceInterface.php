<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Configuration;

use App\Domaining\DTO\DomainConfigurationToolDescriptorDTO;

interface DomainConfigurationToolMetadataServiceInterface
{
    public function descriptor(): DomainConfigurationToolDescriptorDTO;
}
