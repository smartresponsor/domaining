<?php

declare(strict_types=1);

namespace App\Domaining\Service\Publication;

use App\Domaining\DTO\DomainPublicationSnapshotDTO;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Publication\DomainPublicationReadServiceInterface;

final readonly class DomainPublicationReadService implements DomainPublicationReadServiceInterface
{
    public function __construct(
        private DomainPublicationStateRepository $stateRepository,
        private DomainRoutingTargetRepository $targetRepository,
    ) {
    }

    public function snapshot(DomainBindingEntity $binding): DomainPublicationSnapshotDTO
    {
        $state = $this->stateRepository->findOneBy(['binding' => $binding]);
        $target = $this->targetRepository->findOneBy(['binding' => $binding]);

        return new DomainPublicationSnapshotDTO(
            $binding->domainName(),
            $binding->ownerId(),
            $state instanceof DomainPublicationStateEntity ? $state->status() : DomainPublicationStatus::NotReady,
            $target instanceof DomainRoutingTargetEntity ? $target->targetHost() : null,
            $target instanceof DomainRoutingTargetEntity ? $target->targetPath() : null,
        );
    }
}
