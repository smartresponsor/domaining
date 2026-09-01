<?php

declare(strict_types=1);

namespace App\Domaining\Service\Package;

use App\Domaining\Dto\DomainReleasePackageReport;
use App\Domaining\Dto\DomainReleasePackageSurface;
use App\Domaining\Service\Contract\DomainContractGovernanceService;
use App\Domaining\Service\Manifest\DomainReleaseManifestService;
use App\Domaining\Service\Review\DomainReleaseReviewService;
use App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface;
use App\Domaining\ServiceInterface\Export\DomainStateExportServiceInterface;
use App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface;
use App\Domaining\ServiceInterface\Package\DomainReleasePackageServiceInterface;
use App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface;
use App\Domaining\ServiceInterface\Review\DomainReleaseReviewServiceInterface;
use App\Domaining\ServiceInterface\Runtime\DomainRuntimeHandoffServiceInterface;
use DateTimeImmutable;

/**
 * Builds the final provider-neutral Domaining RC package surface.
 *
 * This service intentionally composes exported reports. It does not call DNS
 * providers, mutate certificates, touch reverse proxy configuration, or assume
 * a specific runtime platform.
 */
final readonly class DomainReleasePackageService implements DomainReleasePackageServiceInterface
{
    public const SCHEMA_VERSION = 'domaining.release-package.v1';

    public function __construct(
        private DomainReleaseReviewServiceInterface $releaseReviewService,
        private DomainReleaseManifestServiceInterface $releaseManifestService,
        private DomainContractGovernanceServiceInterface $contractGovernanceService,
        private DomainReleaseGateServiceInterface $releaseGateService,
        private DomainStateExportServiceInterface $stateExportService,
        private DomainRuntimeHandoffServiceInterface $runtimeHandoffService,
    ) {
    }

    public function buildPackage(): DomainReleasePackageReport
    {
        $review = $this->releaseReviewService->buildReport();
        $manifest = $this->releaseManifestService->buildManifest();
        $contractGovernance = $this->contractGovernanceService->buildReport();
        $releaseGate = $this->releaseGateService->evaluate();
        $stateExport = $this->stateExportService->buildExport();
        $runtimeHandoff = $this->runtimeHandoffService->buildReport();

        $packageReady = $review->releaseCandidateReady && $manifest->releaseCandidateReady && $contractGovernance->ready && $releaseGate->passed;

        return new DomainReleasePackageReport(
            self::SCHEMA_VERSION,
            new DateTimeImmutable(),
            $packageReady,
            'Domaining',
            'domaining/domain',
            $this->surface(),
            [
                'php bin/console domaining:release:gate',
                'php bin/console domaining:contract:governance',
                'php bin/console domaining:release:manifest',
                'php bin/console domaining:release:review',
                'php bin/console domaining:release:package',
            ],
            [
                'MANIFEST.json',
                'docs/release/release-gate.adoc',
                'docs/release/release-review.adoc',
                'docs/package/domain-release-package.adoc',
                'docs/api/domaining-endpoint-index.adoc',
                'docs/api/domaining-openapi.adoc',
            ],
            [
                'Domaining packages provider-neutral state and handoff reports only.',
                'Registrar purchase, transfer, renewal, and DNS hosting remain outside Domaining.',
                'Runtime mutation remains owned by a host/runtime provider and must consume Domaining handoff contracts.',
            ],
            [
                'releaseReview' => $review->toArray(),
                'releaseManifest' => $manifest->toArray(),
                'contractGovernance' => $contractGovernance->toArray(),
                'releaseGate' => $releaseGate->toArray(),
                'stateExport' => $stateExport->toArray(),
                'runtimeHandoff' => $runtimeHandoff->toArray(),
            ],
        );
    }

    /**
     * @return list<DomainReleasePackageSurface>
     */
    private function surface(): array
    {
        return [
            new DomainReleasePackageSurface('releasePackage', self::SCHEMA_VERSION, 'Aggregated Domaining RC package surface.', ['GET /domain/release/package'], ['domaining:release:package']),
            new DomainReleasePackageSurface('releaseReview', DomainReleaseReviewService::SCHEMA_VERSION, 'Aggregated RC review report.', ['GET /domain/release/review'], ['domaining:release:review']),
            new DomainReleasePackageSurface('releaseManifest', DomainReleaseManifestService::SCHEMA_VERSION, 'Machine-readable component release manifest.', ['GET /domain/release/manifest'], ['domaining:release:manifest']),
            new DomainReleasePackageSurface('contractGovernance', DomainContractGovernanceService::SCHEMA_VERSION, 'Exported contract inventory and drift checks.', ['GET /domain/contract/governance'], ['domaining:contract:governance']),
            new DomainReleasePackageSurface('runtimeHandoff', DomainContractGovernanceService::RUNTIME_HANDOFF_CONTRACT_VERSION, 'Provider-neutral runtime publication handoff.', ['GET /domain/runtime/handoff'], ['domaining:runtime:handoff-export']),
            new DomainReleasePackageSurface('stateExport', 'domaining.state-export.v1', 'Provider-neutral domain lifecycle state snapshot.', ['GET /domain/export/state'], ['domaining:state:export']),
        ];
    }
}
