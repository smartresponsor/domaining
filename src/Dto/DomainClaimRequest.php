<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

use App\Domaining\Enum\DomainSurfaceType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class DomainClaimRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 253)]
        public string $domainName,
        #[Assert\NotBlank]
        public string $ownerId,
        public DomainSurfaceType $surfaceType = DomainSurfaceType::Tenant,
        #[Assert\NotBlank]
        public string $surfaceKey = 'default',
    ) {
    }
}
