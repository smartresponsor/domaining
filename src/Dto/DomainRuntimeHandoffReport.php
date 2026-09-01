<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainRuntimeHandoffReport
{
    /**
     * @param list<DomainRuntimeHandoffItem> $items
     * @param list<string> $warnings
     */
    public function __construct(
        public array $items,
        public array $warnings = [],
    ) {
    }

    /**
     * @return array{count: int, warnings: list<string>, items: list<array{domainName: string, ownerId: string, surfaceType: string, surfaceKey: string, targetHost: string, targetPath: string, publicationStatus: string, bindingStatus: string, action: string}>}
     */
    public function toArray(): array
    {
        return [
            'count' => count($this->items),
            'warnings' => $this->warnings,
            'items' => array_map(static fn (DomainRuntimeHandoffItem $item): array => $item->toArray(), $this->items),
        ];
    }
}
