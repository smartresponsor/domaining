<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

/**
 * Immutable release gate report for the Domaining component.
 *
 * The report intentionally stays provider-neutral. It does not inspect Cloudflare,
 * registrar, proxy, or certificate-vendor internals; it only verifies the
 * component-owned domain lifecycle state that must be safe before integration
 * with runtime publication providers.
 */
final readonly class DomainReleaseGateReportDTO
{
    /**
     * @param list<string>         $errors
     * @param list<string>         $warnings
     * @param array<string, mixed> $checks
     */
    public function __construct(
        public bool $passed,
        public array $errors,
        public array $warnings,
        public array $checks,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'checks' => $this->checks,
        ];
    }
}
