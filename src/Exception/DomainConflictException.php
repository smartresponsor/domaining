<?php

declare(strict_types=1);

namespace App\Domaining\Exception;

use RuntimeException;

final class DomainConflictException extends RuntimeException
{
    public static function create(string $message = 'Domain binding conflict detected.'): self
    {
        return new self($message);
    }
}
