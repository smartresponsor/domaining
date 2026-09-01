<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainPublicationMode: string
{
    case RuntimeIntent = 'runtime_intent';
    case ManualInstruction = 'manual_instruction';
    case ProviderAutomation = 'provider_automation';
}
