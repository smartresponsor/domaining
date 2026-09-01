<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Audit;

interface DomainAuditServiceInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $domainName, string $action, ?string $actorId = null, array $context = []): void;
}
