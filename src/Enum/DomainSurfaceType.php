<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainSurfaceType: string
{
    case Tenant = 'tenant';
    case Workspace = 'workspace';
    case Application = 'application';
    case Storefront = 'storefront';
    case Landing = 'landing';
    case Api = 'api';
}
