<?php

declare(strict_types=1);

namespace App\Domaining\Service\Diagnostic;

use App\Domaining\Dto\DomainDiagnosticIssue;
use App\Domaining\Dto\DomainDiagnosticReport;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainPublicationState;
use App\Domaining\Entity\DomainRoutingTarget;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface;
use DateTimeImmutable;

final readonly class DomainDiagnosticService implements DomainDiagnosticServiceInterface
{
    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
        private DomainRoutingTargetRepository $routingTargetRepository,
        private DomainVerificationChallengeRepository $challengeRepository,
    ) {
    }

    public function buildReport(): DomainDiagnosticReport
    {
        $now = new DateTimeImmutable();
        $issues = [];

        foreach ($this->bindingRepository->findAll() as $binding) {
            $publicationState = $this->publicationStateRepository->findOneBy(['binding' => $binding]);
            $routingTarget = $this->routingTargetRepository->findOneBy(['binding' => $binding]);

            array_push($issues, ...$this->inspectBinding($binding, $publicationState, $routingTarget));
        }

        foreach ($this->challengeRepository->findAll() as $challenge) {
            array_push($issues, ...$this->inspectChallenge($challenge, $now));
        }

        $severityCount = ['error' => 0, 'warning' => 0, 'info' => 0];
        foreach ($issues as $issue) {
            $severityCount[$issue->severity] = ($severityCount[$issue->severity] ?? 0) + 1;
        }

        return new DomainDiagnosticReport($now, 0 === ($severityCount['error'] ?? 0), $severityCount, $issues);
    }

    /**
     * @return list<DomainDiagnosticIssue>
     */
    private function inspectBinding(DomainBinding $binding, ?DomainPublicationState $publicationState, ?DomainRoutingTarget $routingTarget): array
    {
        $issues = [];
        $publicationStatus = $publicationState?->status() ?? DomainPublicationStatus::NotReady;

        if (DomainBindingStatus::Active === $binding->status() && DomainPublicationStatus::Published !== $publicationStatus) {
            $issues[] = $this->issue('error', 'active_binding_not_published', 'Active domain binding is not marked as published for runtime.', $binding, [
                'publicationStatus' => $publicationStatus->value,
            ]);
        }

        if (in_array($publicationStatus, [DomainPublicationStatus::Ready, DomainPublicationStatus::Published], true) && null === $routingTarget) {
            $issues[] = $this->issue('error', 'publication_without_routing_target', 'Ready or published domain publication has no routing target.', $binding, [
                'publicationStatus' => $publicationStatus->value,
            ]);
        }

        if (DomainBindingStatus::Suspended === $binding->status() && DomainPublicationStatus::Published === $publicationStatus) {
            $issues[] = $this->issue('warning', 'suspended_binding_still_published', 'Suspended domain binding is still marked as published and should be withdrawn from runtime.', $binding);
        }

        if (in_array($binding->status(), [DomainBindingStatus::Verified, DomainBindingStatus::Active], true) && null === $binding->lastVerifiedAt()) {
            $issues[] = $this->issue('warning', 'live_binding_without_verification_timestamp', 'Live domain binding has no last verification timestamp.', $binding);
        }

        if (null !== $routingTarget && $routingTarget->targetHost() === $binding->domainName()) {
            $issues[] = $this->issue('error', 'routing_target_self_loop', 'Routing target host must not point to the same public custom domain.', $binding, [
                'targetHost' => $routingTarget->targetHost(),
                'targetPath' => $routingTarget->targetPath(),
            ]);
        }

        if (null !== $routingTarget && !str_starts_with($routingTarget->targetPath(), '/')) {
            $issues[] = $this->issue('error', 'routing_target_path_invalid', 'Routing target path must start with a slash.', $binding, [
                'targetHost' => $routingTarget->targetHost(),
                'targetPath' => $routingTarget->targetPath(),
            ]);
        }

        return $issues;
    }

    /**
     * @return list<DomainDiagnosticIssue>
     */
    private function inspectChallenge(DomainVerificationChallenge $challenge, DateTimeImmutable $now): array
    {
        $issues = [];
        $claim = $challenge->claim();

        if (DomainVerificationStatus::Pending === $challenge->status() && $challenge->expired($now)) {
            $issues[] = new DomainDiagnosticIssue(
                'warning',
                'pending_challenge_expired',
                'Pending verification challenge is past its expiration timestamp and should be expired or regenerated.',
                $claim->domainName(),
                $claim->ownerId(),
                $claim->surfaceType()->value,
                $claim->surfaceKey(),
                [
                    'challengeId' => (string) $challenge->id(),
                    'expiresAt' => $challenge->expiresAt()->format(DATE_ATOM),
                    'attemptCount' => $challenge->attemptCount(),
                ],
            );
        }

        if (DomainVerificationStatus::Pending === $challenge->status() && $challenge->attemptCount() >= 5) {
            $issues[] = new DomainDiagnosticIssue(
                'warning',
                'verification_retry_threshold_reached',
                'Pending verification challenge has reached repeated DNS check attempts and needs operator review or user guidance.',
                $claim->domainName(),
                $claim->ownerId(),
                $claim->surfaceType()->value,
                $claim->surfaceKey(),
                [
                    'challengeId' => (string) $challenge->id(),
                    'attemptCount' => $challenge->attemptCount(),
                    'lastFailureReason' => $challenge->lastFailureReason(),
                ],
            );
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function issue(string $severity, string $code, string $message, DomainBinding $binding, array $context = []): DomainDiagnosticIssue
    {
        return new DomainDiagnosticIssue(
            $severity,
            $code,
            $message,
            $binding->domainName(),
            $binding->ownerId(),
            $binding->surfaceType()->value,
            $binding->surfaceKey(),
            $context,
        );
    }
}
