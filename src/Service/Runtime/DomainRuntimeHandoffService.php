<?php

declare(strict_types=1);

namespace App\Domaining\Service\Runtime;

use App\Domaining\DTO\DomainRuntimeHandoffItemDTO;
use App\Domaining\DTO\DomainRuntimeHandoffReportDTO;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Runtime\DomainRuntimeHandoffServiceInterface;

final readonly class DomainRuntimeHandoffService implements DomainRuntimeHandoffServiceInterface
{
    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
        private DomainRoutingTargetRepository $routingTargetRepository,
    ) {
    }

    public function buildReport(): DomainRuntimeHandoffReportDTO
    {
        $items = [];
        $warnings = [];

        foreach ($this->bindingRepository->findAll() as $binding) {
            if (!in_array($binding->status(), [DomainBindingStatus::Active, DomainBindingStatus::Suspended], true)) {
                continue;
            }

            $publicationState = $this->publicationStateRepository->findOneBy(['binding' => $binding]);
            $routingTarget = $this->routingTargetRepository->findOneBy(['binding' => $binding]);

            if (null === $routingTarget) {
                $warnings[] = sprintf('Binding "%s" has no routing target and cannot be handed off to runtime.', $binding->domainName());
                continue;
            }

            $publicationStatus = $publicationState?->status() ?? DomainPublicationStatus::NotReady;
            $action = match (true) {
                DomainBindingStatus::Active === $binding->status() && DomainPublicationStatus::Published === $publicationStatus => 'ensure_route',
                DomainBindingStatus::Active === $binding->status() && DomainPublicationStatus::Ready === $publicationStatus => 'publish_route',
                DomainBindingStatus::Suspended === $binding->status() => 'withdraw_route',
                default => 'review',
            };

            $items[] = new DomainRuntimeHandoffItemDTO(
                $binding->domainName(),
                $binding->ownerId(),
                $binding->surfaceType()->value,
                $binding->surfaceKey(),
                $routingTarget->targetHost(),
                $routingTarget->targetPath(),
                $publicationStatus->value,
                $binding->status()->value,
                $action,
            );
        }

        return new DomainRuntimeHandoffReportDTO($items, $warnings);
    }
}
