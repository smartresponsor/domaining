<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

use App\Domaining\Enum\DomainConfigurationTarget;

final readonly class DomainConfigurationVariableDTO
{
    /**
     * @param list<string> $allowedValues
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $description,
        public DomainConfigurationTarget $target,
        public string $type = 'string',
        public bool $required = false,
        public bool $sensitive = false,
        public array $allowedValues = [],
    ) {
    }

    /**
     * @return array{key: string, label: string, description: string, target: string, type: string, required: bool, sensitive: bool, allowedValues: list<string>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'target' => $this->target->value,
            'type' => $this->type,
            'required' => $this->required,
            'sensitive' => $this->sensitive,
            'allowedValues' => $this->allowedValues,
        ];
    }
}
