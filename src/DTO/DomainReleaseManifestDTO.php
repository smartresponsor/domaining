<?php

declare(strict_types=1);

namespace App\Domaining\DTO;

final readonly class DomainReleaseManifestDTO
{
    /**
     * @param list<DomainReleaseManifestContractDTO> $contract
     * @param list<string>                           $capability
     * @param list<string>                           $boundary
     * @param list<string>                           $requiredGate
     * @param list<DomainReleaseManifestCheckDTO>    $check
     */
    public function __construct(
        public string $schemaVersion,
        public \DateTimeImmutable $generatedAt,
        public string $component,
        public string $package,
        public string $namespace,
        public string $businessPrefix,
        public string $databasePrefix,
        public bool $releaseCandidateReady,
        public array $capability,
        public array $boundary,
        public array $requiredGate,
        public array $contract,
        public array $check,
    ) {
    }

    /**
     * @return array{schemaVersion: string, generatedAt: string, component: string, package: string, namespace: string, businessPrefix: string, databasePrefix: string, releaseCandidateReady: bool, capability: list<string>, boundary: list<string>, requiredGate: list<string>, contract: list<array{nameEntity: string, version: string, owner: string, endpoint: list<string>, command: list<string>}>, checkCount: int, check: list<array{severity: string, code: string, message: string, context: array<string, mixed>}>}
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'generatedAt' => $this->generatedAt->format(DATE_ATOM),
            'component' => $this->component,
            'package' => $this->package,
            'namespace' => $this->namespace,
            'businessPrefix' => $this->businessPrefix,
            'databasePrefix' => $this->databasePrefix,
            'releaseCandidateReady' => $this->releaseCandidateReady,
            'capability' => $this->capability,
            'boundary' => $this->boundary,
            'requiredGate' => $this->requiredGate,
            'contract' => array_map(static fn (DomainReleaseManifestContractDTO $contract): array => $contract->toArray(), $this->contract),
            'checkCount' => count($this->check),
            'check' => array_map(static fn (DomainReleaseManifestCheckDTO $check): array => $check->toArray(), $this->check),
        ];
    }
}
