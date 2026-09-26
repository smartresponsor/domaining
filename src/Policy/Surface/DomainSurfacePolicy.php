<?php

declare(strict_types=1);

namespace App\Domaining\Policy\Surface;

use App\Domaining\DTO\DomainSurfacePolicyIssueDTO;
use App\Domaining\DTO\DomainSurfacePolicyReportDTO;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainBindingRepository;

final readonly class DomainSurfacePolicy implements DomainSurfacePolicyInterface
{
    private const DEFAULT_MAX_LIVE_BINDINGS_PER_OWNER = 25;
    private const DEFAULT_MAX_ACTIVE_BINDINGS_PER_SURFACE = 5;

    public function __construct(private DomainBindingRepository $bindingRepository)
    {
    }

    public function buildReport(string $ownerId, ?DomainSurfaceType $surfaceType = null, ?string $surfaceKey = null): DomainSurfacePolicyReportDTO
    {
        $bindings = $this->findBindings($ownerId, $surfaceType, $surfaceKey);
        $issues = [];
        $liveBindings = $this->filterByStatus($bindings, [DomainBindingStatus::Verified, DomainBindingStatus::Active, DomainBindingStatus::Suspended]);
        $activeBindings = $this->filterByStatus($bindings, [DomainBindingStatus::Active]);
        $suspendedBindings = $this->filterByStatus($bindings, [DomainBindingStatus::Suspended]);

        if ('' === trim($ownerId)) {
            $issues[] = $this->issue('error', 'owner_id_empty', 'Owner id must be present before evaluating domain surface policy.');
        }

        if (null !== $surfaceKey && '' === trim($surfaceKey)) {
            $issues[] = $this->issue('error', 'surface_key_empty', 'Surface key must not be empty when a surface type is provided.');
        }

        if (count($liveBindings) > self::DEFAULT_MAX_LIVE_BINDINGS_PER_OWNER) {
            $issues[] = $this->issue('warning', 'owner_live_domain_threshold_exceeded', 'Owner has more live domain bindings than the default policy threshold.', [
                'threshold' => self::DEFAULT_MAX_LIVE_BINDINGS_PER_OWNER,
                'liveBindingCount' => count($liveBindings),
            ]);
        }

        if (null !== $surfaceType && null !== $surfaceKey && count($activeBindings) > self::DEFAULT_MAX_ACTIVE_BINDINGS_PER_SURFACE) {
            $issues[] = $this->issue('warning', 'surface_active_domain_threshold_exceeded', 'Surface has more active custom domains than the default policy threshold.', [
                'threshold' => self::DEFAULT_MAX_ACTIVE_BINDINGS_PER_SURFACE,
                'activeBindingCount' => count($activeBindings),
            ]);
        }

        foreach ($liveBindings as $binding) {
            array_push($issues, ...$this->inspectBinding($binding));
        }

        $severityCount = ['error' => 0, 'warning' => 0, 'info' => 0];
        foreach ($issues as $issue) {
            $severityCount[$issue->severity] = ($severityCount[$issue->severity] ?? 0) + 1;
        }

        return new DomainSurfacePolicyReportDTO(
            new \DateTimeImmutable(),
            0 === ($severityCount['error'] ?? 0),
            $ownerId,
            $surfaceType,
            $surfaceKey,
            count($liveBindings),
            count($activeBindings),
            count($suspendedBindings),
            $severityCount,
            $issues,
        );
    }

    /**
     * @return list<DomainBindingEntity>
     */
    private function findBindings(string $ownerId, ?DomainSurfaceType $surfaceType, ?string $surfaceKey): array
    {
        $criteria = ['ownerId' => $ownerId];
        if (null !== $surfaceType) {
            $criteria['surfaceType'] = $surfaceType;
        }
        if (null !== $surfaceKey) {
            $criteria['surfaceKey'] = $surfaceKey;
        }

        /** @var list<DomainBindingEntity> $bindings */
        $bindings = $this->bindingRepository->findBy($criteria);

        return $bindings;
    }

    /**
     * @param list<DomainBindingEntity> $bindings
     * @param list<DomainBindingStatus> $statuses
     *
     * @return list<DomainBindingEntity>
     */
    private function filterByStatus(array $bindings, array $statuses): array
    {
        return array_values(array_filter(
            $bindings,
            static fn (DomainBindingEntity $binding): bool => in_array($binding->status(), $statuses, true),
        ));
    }

    /**
     * @return list<DomainSurfacePolicyIssueDTO>
     */
    private function inspectBinding(DomainBindingEntity $binding): array
    {
        $issues = [];
        $domainName = $binding->domainName();

        if (str_starts_with($domainName, '*.')) {
            $issues[] = $this->issue('warning', 'wildcard_domain_requires_review', 'Wildcard custom domains require explicit runtime and certificate review.', [
                'domainName' => $domainName,
                'bindingId' => (string) $binding->id(),
            ]);
        }

        if (str_contains($domainName, '_')) {
            $issues[] = $this->issue('error', 'domain_name_contains_underscore', 'Public domain bindings must not contain underscores.', [
                'domainName' => $domainName,
                'bindingId' => (string) $binding->id(),
            ]);
        }

        if (DomainBindingStatus::Suspended === $binding->status()) {
            $issues[] = $this->issue('info', 'suspended_binding_present', 'Suspended binding exists and should remain withdrawn from runtime until reviewed.', [
                'domainName' => $domainName,
                'bindingId' => (string) $binding->id(),
            ]);
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function issue(string $severity, string $code, string $message, array $context = []): DomainSurfacePolicyIssueDTO
    {
        return new DomainSurfacePolicyIssueDTO($severity, $code, $message, $context);
    }
}
