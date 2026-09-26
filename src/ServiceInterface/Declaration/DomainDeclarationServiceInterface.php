<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Declaration;

use App\Domaining\Entity\DomainDeclarationEntity;
use App\Domaining\Enum\DomainApplicationRole;

interface DomainDeclarationServiceInterface
{
    public function declare(
        string $applicationKey,
        string $brandKey,
        string $environment,
        string $domainName,
        DomainApplicationRole $role = DomainApplicationRole::Primary,
    ): DomainDeclarationEntity;
}
