<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Publication;

use App\Domaining\Dto\DomainPublicationSnapshot;
use App\Domaining\Dto\DomainRoutingIntent;
use App\Domaining\Entity\DomainBinding;

interface DomainPublicationServiceInterface
{
    public function prepareRoutingIntent(DomainBinding $binding, string $targetHost, string $targetPath = '/'): DomainRoutingIntent;

    public function markPublished(DomainBinding $binding): DomainPublicationSnapshot;

    public function markWithdrawn(DomainBinding $binding): DomainPublicationSnapshot;
}
