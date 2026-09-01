<?php

declare(strict_types=1);

namespace App\Domaining\Exception;

use RuntimeException;

final class DomainVerificationException extends RuntimeException
{
    public static function create(string $message = 'Domain verification failed.'): self
    {
        return new self($message);
    }
}
