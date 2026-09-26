<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainRuntimeOverlayDTO
{
    public function __construct(
        public string $applicationKey,
        public ?string $brandKey,
        public string $environment,
        public ?string $domainName,
        public ?string $role,
        public ?string $declarationStatus,
        public ?string $bindingStatus,
        public ?string $publicationStatus,
        public ?string $targetHost,
        public ?string $targetPath,
        public bool $customDomainDeclared,
        public bool $customDomainVerified,
        public bool $customDomainReady,
        public bool $customDomainPublished,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'applicationKey' => $this->applicationKey,
            'brandKey' => $this->brandKey,
            'environment' => $this->environment,
            'customDomain' => null === $this->domainName ? null : [
                'domainName' => $this->domainName,
                'role' => $this->role,
                'declarationStatus' => $this->declarationStatus,
                'bindingStatus' => $this->bindingStatus,
                'publicationStatus' => $this->publicationStatus,
                'targetHost' => $this->targetHost,
                'targetPath' => $this->targetPath,
                'declared' => $this->customDomainDeclared,
                'verified' => $this->customDomainVerified,
                'ready' => $this->customDomainReady,
                'published' => $this->customDomainPublished,
            ],
        ];
    }
}
