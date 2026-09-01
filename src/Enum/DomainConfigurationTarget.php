<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainConfigurationTarget: string
{
    case Environment = 'environment';
    case SymfonyParameter = 'symfony_parameter';
    case Secret = 'secret';
    case RuntimeState = 'runtime_state';
}
