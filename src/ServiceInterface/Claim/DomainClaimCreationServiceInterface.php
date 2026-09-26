<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Claim;

use App\Domaining\DTO\DomainClaimRequestDTO;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainDeclarationEntity;

interface DomainClaimCreationServiceInterface
{
    public function createClaim(DomainClaimRequestDTO $request): DomainClaimEntity;

    public function createForDeclaration(DomainDeclarationEntity $declaration, string $ownerId): DomainClaimEntity;
}
