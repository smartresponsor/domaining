<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainPublicationState;

interface DomainPublicationStateRepositoryInterface
{
    public function findOneForBinding(DomainBinding $binding): ?DomainPublicationState;
}
