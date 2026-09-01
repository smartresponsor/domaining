<?php

declare(strict_types=1);

namespace App\Domaining\Dto;

final readonly class DomainTemplateRenderResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public bool $rendered,
        public string $surface,
        public ?string $template,
        public array $payload,
        public ?string $html = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rendered' => $this->rendered,
            'surface' => $this->surface,
            'template' => $this->template,
            'payload' => $this->payload,
            'html' => $this->html,
        ];
    }
}
