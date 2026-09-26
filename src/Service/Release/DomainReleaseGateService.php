<?php

declare(strict_types=1);

namespace App\Domaining\Service\Release;

use App\Domaining\DTO\DomainReleaseGateReportDTO;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface;

final readonly class DomainReleaseGateService implements DomainReleaseGateServiceInterface
{
    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private DomainVerificationChallengeRepository $challengeRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
        private DomainRoutingTargetRepository $routingTargetRepository,
    ) {
    }

    public function evaluate(): DomainReleaseGateReportDTO
    {
        $errors = [];
        $warnings = [];

        $activeBindings = $this->bindingRepository->count(['status' => DomainBindingStatus::Active]);
        $verifiedBindings = $this->bindingRepository->count(['status' => DomainBindingStatus::Verified]);
        $suspendedBindings = $this->bindingRepository->count(['status' => DomainBindingStatus::Suspended]);
        $activeBindingsWithoutVerification = $this->bindingRepository->count([
            'status' => DomainBindingStatus::Active,
            'lastVerifiedAt' => null,
        ]);
        $failedChallenges = $this->challengeRepository->count(['status' => DomainVerificationStatus::Failed]);
        $readyPublications = $this->publicationStateRepository->count(['status' => DomainPublicationStatus::Ready]);
        $publishedPublications = $this->publicationStateRepository->count(['status' => DomainPublicationStatus::Published]);
        $routingTargets = $this->routingTargetRepository->count([]);

        if ($activeBindings > 0 && 0 === $publishedPublications) {
            $errors[] = 'Active domain bindings exist, but no publication state is marked as published.';
        }

        if ($activeBindingsWithoutVerification > 0) {
            $errors[] = 'Active domain bindings exist without recorded ownership verification timestamps.';
        }

        if ($readyPublications > 0 && 0 === $routingTargets) {
            $errors[] = 'Publication states are ready, but routing targets are missing.';
        }

        if ($failedChallenges > 0) {
            $warnings[] = 'Failed verification challenges exist; review them before release promotion.';
        }

        if ($suspendedBindings > 0) {
            $warnings[] = 'Suspended domain bindings exist; confirm that runtime publication is withdrawn.';
        }

        return new DomainReleaseGateReportDTO([] === $errors, $errors, $warnings, [
            'active_bindings' => $activeBindings,
            'active_bindings_without_verification' => $activeBindingsWithoutVerification,
            'verified_bindings' => $verifiedBindings,
            'suspended_bindings' => $suspendedBindings,
            'failed_challenges' => $failedChallenges,
            'ready_publications' => $readyPublications,
            'published_publications' => $publishedPublications,
            'routing_targets' => $routingTargets,
        ]);
    }
}
