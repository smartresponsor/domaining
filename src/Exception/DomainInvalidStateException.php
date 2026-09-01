<?php

declare(strict_types=1);

namespace App\Domaining\Exception;

use RuntimeException;

final class DomainInvalidStateException extends RuntimeException
{
    public static function create(string $message = 'Domain lifecycle transition is not allowed.'): self
    {
        return new self($message);
    }
}
