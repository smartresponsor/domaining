<?php

declare(strict_types=1);

namespace App\Domaining\Lifecycle;

/**
 * Lifecycle guard for domain claim.
 *
 * The policy is intentionally framework-free: entities/services can call it
 * without introducing cross-component Doctrine dependencies.
 */
final class DomainClaimLifecyclePolicy
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['pending_verification', 'cancelled'],
        'pending_verification' => ['verified', 'failed', 'expired', 'cancelled'],
        'failed' => ['pending_verification', 'cancelled'],
        'verified' => ['binding_ready', 'revoked'],
        'binding_ready' => ['bound', 'revoked'],
        'bound' => ['suspended', 'released', 'revoked'],
        'suspended' => ['bound', 'released', 'revoked'],
        'expired' => ['pending_verification', 'archived'],
        'released' => ['archived'],
        'revoked' => ['archived'],
        'cancelled' => [],
        'archived' => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        if ($from === $to) {
            return true;
        }

        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function assertCanTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new \DomainException(sprintf(
                'Invalid domain claim lifecycle transition from "%s" to "%s".',
                $from,
                $to,
            ));
        }
    }

    /** @return list<string> */
    public function allowedNextStatuses(string $from): array
    {
        return self::TRANSITIONS[self::normalize($from)] ?? [];
    }

    private static function normalize(string $status): string
    {
        return strtolower(trim($status));
    }
}
