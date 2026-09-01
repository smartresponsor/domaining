<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Publication;

use App\Domaining\Dto\DomainPublicationSnapshot;
use App\Domaining\Entity\DomainBinding;

interface DomainPublicationReadServiceInterface
{
    public function snapshot(DomainBinding $binding): DomainPublicationSnapshot;
}
