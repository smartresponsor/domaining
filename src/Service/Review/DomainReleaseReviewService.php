<?php

declare(strict_types=1);

namespace App\Domaining\Service\Review;

use App\Domaining\DTO\DomainContractGovernanceIssueDTO;
use App\Domaining\DTO\DomainDiagnosticIssueDTO;
use App\Domaining\DTO\DomainReleaseManifestCheckDTO;
use App\Domaining\DTO\DomainReleaseReviewIssueDTO;
use App\Domaining\DTO\DomainReleaseReviewReportDTO;
use App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface;
use App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface;
use App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface;
use App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface;
use App\Domaining\ServiceInterface\Review\DomainReleaseReviewServiceInterface;

/**
 * Aggregates Domaining RC gates into one review surface.
 *
 * The service intentionally reviews Domaining-owned contracts and lifecycle state
 * only. Provider-specific registrar, DNS host, TLS issuer, or edge-router state
 * remains outside the component boundary and is represented by handoff contracts.
 */
final readonly class DomainReleaseReviewService implements DomainReleaseReviewServiceInterface
{
    public const SCHEMA_VERSION = 'domaining.release-review.v1';

    public function __construct(
        private DomainReleaseGateServiceInterface $releaseGateService,
        private DomainContractGovernanceServiceInterface $contractGovernanceService,
        private DomainReleaseManifestServiceInterface $manifestService,
        private DomainDiagnosticServiceInterface $diagnosticService,
    ) {
    }

    public function buildReport(): DomainReleaseReviewReportDTO
    {
        $releaseGate = $this->releaseGateService->evaluate();
        $contractGovernance = $this->contractGovernanceService->buildReport();
        $manifest = $this->manifestService->buildManifest();
        $diagnostic = $this->diagnosticService->buildReport();

        $issues = [
            ...$this->issueFromReleaseGate($releaseGate->errors, 'error'),
            ...$this->issueFromReleaseGate($releaseGate->warnings, 'warning'),
            ...$this->issueFromContractGovernance($contractGovernance->issue),
            ...$this->issueFromManifest($manifest->check),
            ...$this->issueFromDiagnostic($diagnostic->issues),
        ];

        if (!$contractGovernance->ready) {
            $issues[] = $this->issue('error', 'contract_governance_not_ready', 'Contract governance is not ready for RC promotion.');
        }

        if (!$manifest->releaseCandidateReady) {
            $issues[] = $this->issue('error', 'release_manifest_not_ready', 'Release manifest is not ready for RC promotion.');
        }

        if (!$diagnostic->pass) {
            $issues[] = $this->issue('warning', 'diagnostic_report_has_findings', 'Diagnostic report has lifecycle findings that should be reviewed before RC publication.', [
                'severityCount' => $diagnostic->severityCount,
            ]);
        }

        $hasError = array_any($issues, static fn (DomainReleaseReviewIssueDTO $issue): bool => 'error' === $issue->severity);

        return new DomainReleaseReviewReportDTO(
            self::SCHEMA_VERSION,
            new \DateTimeImmutable(),
            !$hasError,
            $issues,
            [
                'releaseGate' => $releaseGate->toArray(),
                'contractGovernance' => $contractGovernance->toArray(),
                'releaseManifest' => $manifest->toArray(),
                'diagnostic' => $diagnostic->toArray(),
            ],
        );
    }

    /**
     * @param list<string> $message
     *
     * @return list<DomainReleaseReviewIssueDTO>
     */
    private function issueFromReleaseGate(array $message, string $severity): array
    {
        return array_map(
            fn (string $text): DomainReleaseReviewIssueDTO => $this->issue($severity, 'release_gate_'.$severity, $text),
            $message,
        );
    }

    /**
     * @param list<DomainContractGovernanceIssueDTO> $issue
     *
     * @return list<DomainReleaseReviewIssueDTO>
     */
    private function issueFromContractGovernance(array $issue): array
    {
        return array_map(
            fn (DomainContractGovernanceIssueDTO $item): DomainReleaseReviewIssueDTO => $this->issue($item->severity, 'contract_'.$item->code, $item->message, $item->context),
            $issue,
        );
    }

    /**
     * @param list<DomainReleaseManifestCheckDTO> $check
     *
     * @return list<DomainReleaseReviewIssueDTO>
     */
    private function issueFromManifest(array $check): array
    {
        return array_map(
            fn (DomainReleaseManifestCheckDTO $item): DomainReleaseReviewIssueDTO => $this->issue($item->severity, 'manifest_'.$item->code, $item->message, $item->context),
            $check,
        );
    }

    /**
     * @param list<DomainDiagnosticIssueDTO> $issue
     *
     * @return list<DomainReleaseReviewIssueDTO>
     */
    private function issueFromDiagnostic(array $issue): array
    {
        return array_map(
            fn (DomainDiagnosticIssueDTO $item): DomainReleaseReviewIssueDTO => $this->issue($item->severity, 'diagnostic_'.$item->code, $item->message, $item->context),
            $issue,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function issue(string $severity, string $code, string $message, array $context = []): DomainReleaseReviewIssueDTO
    {
        return new DomainReleaseReviewIssueDTO($severity, $code, $message, $context);
    }
}
