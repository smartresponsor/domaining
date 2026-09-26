<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

/**
 * Machine-readable release review issue for RC promotion.
 */
final readonly class DomainReleaseReviewIssueDTO
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $severity,
        public string $code,
        public string $message,
        public array $context = [],
    ) {
    }

    /** @return array{severity: string, code: string, message: string, context: array<string, mixed>} */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'code' => $this->code,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
