<?php

declare(strict_types=1);

namespace App\Domaining\Service\Export;

use App\Domaining\DTO\DomainStateExportBindingDTO;
use App\Domaining\DTO\DomainStateExportReportDTO;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Export\DomainStateExportServiceInterface;

final readonly class DomainStateExportService implements DomainStateExportServiceInterface
{
    public const SCHEMA_VERSION = 'domaining.state-export.v1';

    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
        private DomainRoutingTargetRepository $routingTargetRepository,
    ) {
    }

    public function buildExport(): DomainStateExportReportDTO
    {
        $bindings = [];
        $warnings = [];

        foreach ($this->bindingRepository->findAll() as $binding) {
            $publicationState = $this->publicationStateRepository->findOneBy(['binding' => $binding]);
            $routingTarget = $this->routingTargetRepository->findOneBy(['binding' => $binding]);
            $publicationStatus = $publicationState?->status();

            $reviewState = $this->reviewState(
                $binding->status(),
                $publicationStatus,
                null !== $routingTarget,
                null !== $binding->lastVerifiedAt(),
            );

            if ('blocked' === $reviewState) {
                $warnings[] = sprintf('Binding "%s" requires review before runtime publication export can be trusted.', $binding->domainName());
            }

            $bindings[] = new DomainStateExportBindingDTO(
                $binding->domainName(),
                $binding->ownerId(),
                $binding->surfaceType()->value,
                $binding->surfaceKey(),
                $binding->status()->value,
                $publicationStatus?->value,
                $routingTarget?->targetHost(),
                $routingTarget?->targetPath(),
                $binding->lastVerifiedAt()?->format(DATE_ATOM),
                $reviewState,
            );
        }

        return new DomainStateExportReportDTO(self::SCHEMA_VERSION, new \DateTimeImmutable(), $bindings, $warnings);
    }

    private function reviewState(
        DomainBindingStatus $bindingStatus,
        ?DomainPublicationStatus $publicationStatus,
        bool $hasRoutingTarget,
        bool $hasVerificationTimestamp,
    ): string {
        if (DomainBindingStatus::Removed === $bindingStatus) {
            return 'historical';
        }

        if (DomainBindingStatus::Active === $bindingStatus && DomainPublicationStatus::Published === $publicationStatus && $hasRoutingTarget && $hasVerificationTimestamp) {
            return 'runtime_ready';
        }

        if (DomainBindingStatus::Verified === $bindingStatus && $hasVerificationTimestamp) {
            return 'binding_ready';
        }

        if (DomainBindingStatus::Suspended === $bindingStatus) {
            return 'withdrawal_review';
        }

        return 'blocked';
    }
}
