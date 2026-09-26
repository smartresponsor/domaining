<?php

declare(strict_types=1);

namespace App\Domaining\Service\Contract;

use App\Domaining\DTO\DomainContractGovernanceIssueDTO;
use App\Domaining\DTO\DomainContractGovernanceReportDTO;
use App\Domaining\Service\Export\DomainStateExportService;
use App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface;

final readonly class DomainContractGovernanceService implements DomainContractGovernanceServiceInterface
{
    public const SCHEMA_VERSION = 'domaining.contract-governance.v1';
    public const RUNTIME_HANDOFF_CONTRACT_VERSION = 'domaining.runtime-handoff.v1';
    public const SURFACE_POLICY_CONTRACT_VERSION = 'domaining.surface-policy.v1';
    public const DIAGNOSTIC_CONTRACT_VERSION = 'domaining.diagnostic-report.v1';
    public const RELEASE_GATE_CONTRACT_VERSION = 'domaining.release-gate.v1';
    public const RELEASE_MANIFEST_CONTRACT_VERSION = 'domaining.release-manifest.v1';
    public const RELEASE_REVIEW_CONTRACT_VERSION = 'domaining.release-review.v1';
    public const RELEASE_PACKAGE_CONTRACT_VERSION = 'domaining.release-package.v1';

    /**
     * @return list<string>
     */
    private function endpoint(): array
    {
        return [
            'GET /domain/export/state',
            'GET /domain/runtime/handoff',
            'GET /domain/policy/surface/{ownerId}',
            'GET /domain/diagnostic/report',
            'GET /domain/release/gate',
            'GET /domain/contract/governance',
            'GET /domain/release/manifest',
            'GET /domain/release/review',
            'GET /domain/release/package',
        ];
    }

    /**
     * @return list<string>
     */
    private function command(): array
    {
        return [
            'domaining:state:export',
            'domaining:runtime:handoff-export',
            'domaining:policy:surface-report',
            'domaining:diagnostic:report',
            'domaining:release:gate',
            'domaining:contract:governance',
            'domaining:release:manifest',
            'domaining:release:review',
            'domaining:release:package',
        ];
    }

    public function buildReport(): DomainContractGovernanceReportDTO
    {
        $contractVersion = [
            'stateExport' => DomainStateExportService::SCHEMA_VERSION,
            'runtimeHandoff' => self::RUNTIME_HANDOFF_CONTRACT_VERSION,
            'surfacePolicy' => self::SURFACE_POLICY_CONTRACT_VERSION,
            'diagnosticReport' => self::DIAGNOSTIC_CONTRACT_VERSION,
            'releaseGate' => self::RELEASE_GATE_CONTRACT_VERSION,
            'contractGovernance' => self::SCHEMA_VERSION,
            'releaseManifest' => self::RELEASE_MANIFEST_CONTRACT_VERSION,
            'releaseReview' => self::RELEASE_REVIEW_CONTRACT_VERSION,
            'releasePackage' => self::RELEASE_PACKAGE_CONTRACT_VERSION,
        ];

        $issues = $this->inspectContractVersion($contractVersion);
        $issues = [...$issues, ...$this->inspectEndpointCoverage()];

        $hasError = array_any($issues, static fn (DomainContractGovernanceIssueDTO $issue): bool => 'error' === $issue->severity);

        return new DomainContractGovernanceReportDTO(
            self::SCHEMA_VERSION,
            new \DateTimeImmutable(),
            !$hasError,
            $contractVersion,
            $this->endpoint(),
            $this->command(),
            $issues,
        );
    }

    /**
     * @param array<string, string> $contractVersion
     *
     * @return list<DomainContractGovernanceIssueDTO>
     */
    private function inspectContractVersion(array $contractVersion): array
    {
        $issues = [];

        foreach ($contractVersion as $nameEntity => $version) {
            if (!str_starts_with($version, 'domaining.')) {
                $issues[] = $this->issue('error', 'contract_version_not_domaining_scoped', 'Contract version must be scoped by the domaining prefix.', [
                    'nameEntity' => $nameEntity,
                    'version' => $version,
                ]);
            }

            if (!str_ends_with($version, '.v1')) {
                $issues[] = $this->issue('warning', 'contract_version_not_v1', 'Contract version is not on the initial v1 compatibility line.', [
                    'nameEntity' => $nameEntity,
                    'version' => $version,
                ]);
            }
        }

        return $issues;
    }

    /**
     * @return list<DomainContractGovernanceIssueDTO>
     */
    private function inspectEndpointCoverage(): array
    {
        $issues = [];
        $endpointText = implode("\n", $this->endpoint());
        $commandText = implode("\n", $this->command());

        foreach (['export', 'runtime', 'policy', 'diagnostic', 'release', 'contract', 'manifest'] as $surface) {
            if (!str_contains($endpointText, $surface)) {
                $issues[] = $this->issue('error', 'contract_endpoint_surface_missing', 'Expected contract surface is missing from endpoint inventory.', [
                    'surface' => $surface,
                ]);
            }

            if (!str_contains($commandText, $surface)) {
                $issues[] = $this->issue('warning', 'contract_command_surface_missing', 'Expected contract surface is missing from CLI inventory.', [
                    'surface' => $surface,
                ]);
            }
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function issue(string $severity, string $code, string $message, array $context = []): DomainContractGovernanceIssueDTO
    {
        return new DomainContractGovernanceIssueDTO($severity, $code, $message, $context);
    }
}
