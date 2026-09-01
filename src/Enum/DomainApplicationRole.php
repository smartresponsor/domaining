<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainApplicationRole: string
{
    case Primary = 'primary';
    case Alias = 'alias';
    case Api = 'api';
    case Authentication = 'authentication';
    case MobileLink = 'mobile_link';
}
