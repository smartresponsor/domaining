<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainConfigurationToolDescriptor
{
    /**
     * @param list<DomainConfigurationVariable> $variables
     * @param list<string> $capabilities
     * @param array<string, string> $metadata
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $section,
        public string $description,
        public array $variables,
        public array $capabilities = [],
        public array $metadata = [],
    ) {
    }

    /**
     * @return array{key: string, label: string, section: string, description: string, capabilities: list<string>, variables: list<array<string, mixed>>, metadata: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'section' => $this->section,
            'description' => $this->description,
            'capabilities' => $this->capabilities,
            'variables' => array_map(static fn (DomainConfigurationVariable $variable): array => $variable->toArray(), $this->variables),
            'metadata' => $this->metadata,
        ];
    }
}
