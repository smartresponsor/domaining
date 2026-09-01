<?php

declare(strict_types=1);

namespace App\Domaining\Event;

final readonly class DomainBindingSuspended
{
    public function __construct(public string $domainName, public string $ownerId)
    {
    }
}
