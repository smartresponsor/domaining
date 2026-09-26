<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Publication;

use App\Domaining\DTO\DomainPublicationSnapshotDTO;
use App\Domaining\DTO\DomainRoutingIntentDTO;
use App\Domaining\Entity\DomainBindingEntity;

interface DomainPublicationServiceInterface
{
    public function prepareRoutingIntent(DomainBindingEntity $binding, string $targetHost, string $targetPath = '/'): DomainRoutingIntentDTO;

    public function markPublished(DomainBindingEntity $binding): DomainPublicationSnapshotDTO;

    public function markWithdrawn(DomainBindingEntity $binding): DomainPublicationSnapshotDTO;
}
