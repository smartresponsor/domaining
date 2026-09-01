<?php

declare(strict_types=1);

namespace App\Domaining\Value;

use Random\RandomException;

final readonly class DomainVerificationToken
{
    public function __construct(public string $value)
    {
    }

    /**
     * Creates a non-predictable token suitable for DNS ownership challenge records.
     *
     * @throws RandomException
     */
    public static function create(): self
    {
        return new self('sr-domain-verification=' . bin2hex(random_bytes(24)));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
