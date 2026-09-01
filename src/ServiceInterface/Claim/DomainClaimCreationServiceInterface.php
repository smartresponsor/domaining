<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Claim;

use App\Domaining\Dto\DomainClaimRequest;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Entity\DomainDeclaration;

interface DomainClaimCreationServiceInterface
{
    public function createClaim(DomainClaimRequest $request): DomainClaim;

    public function createForDeclaration(DomainDeclaration $declaration, string $ownerId): DomainClaim;
}
