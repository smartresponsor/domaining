<?php

declare(strict_types=1);

namespace App\Domaining\Service\Manifest;

use App\Domaining\Dto\DomainReleaseManifest;
use App\Domaining\Dto\DomainReleaseManifestCheck;
use App\Domaining\Dto\DomainReleaseManifestContract;
use App\Domaining\Service\Contract\DomainContractGovernanceService;
use App\Domaining\Service\Export\DomainStateExportService;
use App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface;
use DateTimeImmutable;

final readonly class DomainReleaseManifestService implements DomainReleaseManifestServiceInterface
{
    public const SCHEMA_VERSION = 'domaining.release-manifest.v1';

    public function buildManifest(): DomainReleaseManifest
    {
        $contract = $this->contract();
        $check = [
            ...$this->inspectContract($contract),
            ...$this->inspectBoundary(),
        ];
        $hasError = array_any($check, static fn (DomainReleaseManifestCheck $check): bool => 'error' === $check->severity);

        return new DomainReleaseManifest(
            self::SCHEMA_VERSION,
            new DateTimeImmutable(),
            'Domaining',
            'domaining/domain',
            'App\\Domaining',
            'Domain*',
            'domain_',
            !$hasError,
            [
                'external-domain-claim',
                'provider-neutral-dns-instruction',
                'dns-ownership-verification',
                'tenant-surface-domain-binding',
                'publication-readiness',
                'runtime-handoff-export',
                'surface-policy-report',
                'diagnostic-report',
                'release-gate',
                'contract-governance',
                'release-review',
                'release-package',
            ],
            [
                'Domaining does not register, sell, transfer, or renew domains.',
                'Domaining does not own provider-specific Cloudflare, Caddy, Nginx, Traefik, or Kubernetes mutation logic.',
                'Domaining emits provider-neutral routing intent and runtime handoff data only.',
                'Administering may render and operate Domaining surfaces but does not own the domain lifecycle.',
                'Accessing/Rolling may authorize Domaining actions but do not verify DNS ownership.',
            ],
            [
                'domaining:release:gate must pass before RC publication.',
                'domaining:contract:governance must report no errors before RC publication.',
                'domaining:diagnostic:report should be reviewed before runtime-provider integration.',
                'domaining:runtime:handoff-export must remain provider-neutral.',
                'domaining:release:review must be checked before RC publication.',
                'domaining:release:package must be captured as the final provider-neutral RC package report.',
            ],
            $contract,
            $check,
        );
    }

    /**
     * @return list<DomainReleaseManifestContract>
     */
    private function contract(): array
    {
        return [
            new DomainReleaseManifestContract('stateExport', DomainStateExportService::SCHEMA_VERSION, 'Domaining', ['GET /domain/export/state'], ['domaining:state:export']),
            new DomainReleaseManifestContract('runtimeHandoff', DomainContractGovernanceService::RUNTIME_HANDOFF_CONTRACT_VERSION, 'Domaining', ['GET /domain/runtime/handoff'], ['domaining:runtime:handoff-export']),
            new DomainReleaseManifestContract('surfacePolicy', DomainContractGovernanceService::SURFACE_POLICY_CONTRACT_VERSION, 'Domaining', ['GET /domain/policy/surface/{ownerId}'], ['domaining:policy:surface-report']),
            new DomainReleaseManifestContract('diagnosticReport', DomainContractGovernanceService::DIAGNOSTIC_CONTRACT_VERSION, 'Domaining', ['GET /domain/diagnostic/report'], ['domaining:diagnostic:report']),
            new DomainReleaseManifestContract('releaseGate', DomainContractGovernanceService::RELEASE_GATE_CONTRACT_VERSION, 'Domaining', ['GET /domain/release/gate'], ['domaining:release:gate']),
            new DomainReleaseManifestContract('contractGovernance', DomainContractGovernanceService::SCHEMA_VERSION, 'Domaining', ['GET /domain/contract/governance'], ['domaining:contract:governance']),
            new DomainReleaseManifestContract('releaseManifest', self::SCHEMA_VERSION, 'Domaining', ['GET /domain/release/manifest'], ['domaining:release:manifest']),
            new DomainReleaseManifestContract('releaseReview', 'domaining.release-review.v1', 'Domaining', ['GET /domain/release/review'], ['domaining:release:review']),
            new DomainReleaseManifestContract('releasePackage', 'domaining.release-package.v1', 'Domaining', ['GET /domain/release/package'], ['domaining:release:package']),
        ];
    }

    /**
     * @param list<DomainReleaseManifestContract> $contract
     * @return list<DomainReleaseManifestCheck>
     */
    private function inspectContract(array $contract): array
    {
        $checks = [];
        $names = [];

        foreach ($contract as $item) {
            if (isset($names[$item->nameEntity])) {
                $checks[] = new DomainReleaseManifestCheck('error', 'duplicate_contract_name', 'Release manifest contract names must be unique.', ['nameEntity' => $item->nameEntity]);
            }
            $names[$item->nameEntity] = true;

            if (!str_starts_with($item->version, 'domaining.')) {
                $checks[] = new DomainReleaseManifestCheck('error', 'contract_version_not_domaining_scoped', 'Contract version must be scoped by the domaining prefix.', ['nameEntity' => $item->nameEntity, 'version' => $item->version]);
            }

            if ([] === $item->endpoint) {
                $checks[] = new DomainReleaseManifestCheck('warning', 'contract_endpoint_missing', 'Contract has no HTTP endpoint surface.', ['nameEntity' => $item->nameEntity]);
            }
        }

        return $checks;
    }

    /**
     * @return list<DomainReleaseManifestCheck>
     */
    private function inspectBoundary(): array
    {
        return [
            new DomainReleaseManifestCheck('info', 'registrar_boundary_declared', 'Registrar/provider ownership is explicitly out of scope.'),
            new DomainReleaseManifestCheck('info', 'runtime_mutation_boundary_declared', 'Runtime mutation remains outside Domaining and is represented through provider-neutral handoff.'),
        ];
    }
}
