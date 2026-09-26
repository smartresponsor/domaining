<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Publication;

use App\Domaining\DTO\DomainPublicationSnapshotDTO;
use App\Domaining\Entity\DomainBindingEntity;

interface DomainPublicationReadServiceInterface
{
    public function snapshot(DomainBindingEntity $binding): DomainPublicationSnapshotDTO;
}
