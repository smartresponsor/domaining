<?php

declare(strict_types=1);

namespace App\Domaining\Service\Publication;

use App\Domaining\Dto\DomainPublicationSnapshot;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainPublicationState;
use App\Domaining\Entity\DomainRoutingTarget;
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

    public function snapshot(DomainBinding $binding): DomainPublicationSnapshot
    {
        $state = $this->stateRepository->findOneBy(['binding' => $binding]);
        $target = $this->targetRepository->findOneBy(['binding' => $binding]);

        return new DomainPublicationSnapshot(
            $binding->domainName(),
            $binding->ownerId(),
            $state instanceof DomainPublicationState ? $state->status() : DomainPublicationStatus::NotReady,
            $target instanceof DomainRoutingTarget ? $target->targetHost() : null,
            $target instanceof DomainRoutingTarget ? $target->targetPath() : null,
        );
    }
}
