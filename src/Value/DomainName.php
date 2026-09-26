<?php

declare(strict_types=1);

namespace App\Domaining\Value;

final readonly class DomainName
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = self::normalize($value);
        if (!self::isValid($normalized)) {
            throw new \InvalidArgumentException(sprintf('Invalid domain nameEntity "%s".', $value));
        }
        $this->value = $normalized;
    }

    public static function normalize(string $domainName): string
    {
        return trim(strtolower($domainName), " \t\n\r\0\x0B.");
    }

    public static function isValid(string $domainName): bool
    {
        if ('' === $domainName || 253 < strlen($domainName)) {
            return false;
        }

        return 1 === preg_match('/^(?=.{1,253}$)(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/', $domainName);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
