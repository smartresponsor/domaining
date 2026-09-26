<?php

declare(strict_types=1);

namespace App\Domaining\RepositoryInterface;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;

interface DomainPublicationStateRepositoryInterface
{
    public function findOneForBinding(DomainBindingEntity $binding): ?DomainPublicationStateEntity;
}
