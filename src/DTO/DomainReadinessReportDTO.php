<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainReadinessReportDTO
{
    /**
     * @param array<string, int> $bindingStatusCount
     * @param array<string, int> $verificationStatusCount
     * @param array<string, int> $publicationStatusCount
     * @param list<string>       $warnings
     */
    public function __construct(
        public bool $ready,
        public array $bindingStatusCount,
        public array $verificationStatusCount,
        public array $publicationStatusCount,
        public array $warnings,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ready' => $this->ready,
            'bindingStatusCount' => $this->bindingStatusCount,
            'verificationStatusCount' => $this->verificationStatusCount,
            'publicationStatusCount' => $this->publicationStatusCount,
            'warnings' => $this->warnings,
        ];
    }
}
