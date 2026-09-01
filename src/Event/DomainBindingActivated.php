<?php

declare(strict_types=1);

namespace App\Domaining\Event;

final readonly class DomainBindingActivated
{
    public function __construct(public string $domainName, public string $ownerId)
    {
    }
}
