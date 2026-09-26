<?php

declare(strict_types=1);

namespace App\Domaining\Policy\Lifecycle;

/**
 * Guards allowed lifecycle transitions for domain binding.
 *
 * This policy is intentionally string-based for now so it can wrap existing
 * entity status fields without forcing a schema migration. Core status enums
 * can be introduced per component once runtime metadata validation is green.
 */
final class DomainBindingLifecyclePolicy
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'claimed' => ['verification_pending', 'withdrawn'],
        'verification_pending' => ['verified', 'failed', 'withdrawn'],
        'failed' => ['verification_pending', 'withdrawn'],
        'verified' => ['bound', 'withdrawn'],
        'bound' => ['published', 'suspended', 'removed'],
        'published' => ['suspended', 'removed'],
        'suspended' => ['published', 'removed'],
        'removed' => [],
        'withdrawn' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function assertCanTransition(string $from, string $to): void
    {
        if (!self::canTransition($from, $to)) {
            throw new \DomainException(sprintf('Invalid Domaining lifecycle transition from "%s" to "%s".', $from, $to));
        }
    }

    /** @return list<string> */
    public static function allowedTargets(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }
}
