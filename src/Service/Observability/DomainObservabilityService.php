<?php

declare(strict_types=1);

namespace App\Domaining\Service\Observability;

use App\Domaining\Dto\DomainReadinessReport;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Observability\DomainObservabilityServiceInterface;

final readonly class DomainObservabilityService implements DomainObservabilityServiceInterface
{
    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private DomainVerificationChallengeRepository $challengeRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
    ) {
    }

    public function readinessReport(): DomainReadinessReport
    {
        $bindingStatusCount = $this->countBindingStatus();
        $verificationStatusCount = $this->countVerificationStatus();
        $publicationStatusCount = $this->countPublicationStatus();
        $warnings = [];

        if (($bindingStatusCount[DomainBindingStatus::Active->value] ?? 0) > 0 && 0 === ($publicationStatusCount[DomainPublicationStatus::Published->value] ?? 0)) {
            $warnings[] = 'Active domain bindings exist but no publication state is marked as published.';
        }

        if (($verificationStatusCount[DomainVerificationStatus::Failed->value] ?? 0) > 0) {
            $warnings[] = 'Failed verification challenges exist and should be reviewed.';
        }

        return new DomainReadinessReport([] === $warnings, $bindingStatusCount, $verificationStatusCount, $publicationStatusCount, $warnings);
    }

    /**
     * @return array<string, mixed>
     */
    public function metricSnapshot(): array
    {
        return [
            'domain_binding_status_total' => $this->countBindingStatus(),
            'domain_verification_status_total' => $this->countVerificationStatus(),
            'domain_publication_status_total' => $this->countPublicationStatus(),
        ];
    }

    /** @return array<string, int> */
    private function countBindingStatus(): array
    {
        $count = [];
        foreach (DomainBindingStatus::cases() as $status) {
            $count[$status->value] = $this->bindingRepository->count(['status' => $status]);
        }

        return $count;
    }

    /** @return array<string, int> */
    private function countVerificationStatus(): array
    {
        $count = [];
        foreach (DomainVerificationStatus::cases() as $status) {
            $count[$status->value] = $this->challengeRepository->count(['status' => $status]);
        }

        return $count;
    }

    /** @return array<string, int> */
    private function countPublicationStatus(): array
    {
        $count = [];
        foreach (DomainPublicationStatus::cases() as $status) {
            $count[$status->value] = $this->publicationStateRepository->count(['status' => $status]);
        }

        return $count;
    }
}
