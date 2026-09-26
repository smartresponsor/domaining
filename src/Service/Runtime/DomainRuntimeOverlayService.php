<?php

declare(strict_types=1);

namespace App\Domaining\Service\Runtime;

use App\Domaining\DTO\DomainRuntimeOverlayDTO;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\RepositoryInterface\DomainBindingRepositoryInterface;
use App\Domaining\RepositoryInterface\DomainDeclarationRepositoryInterface;
use App\Domaining\RepositoryInterface\DomainPublicationStateRepositoryInterface;
use App\Domaining\RepositoryInterface\DomainRoutingTargetRepositoryInterface;
use App\Domaining\ServiceInterface\Runtime\DomainRuntimeOverlayServiceInterface;

final readonly class DomainRuntimeOverlayService implements DomainRuntimeOverlayServiceInterface
{
    public function __construct(
        private DomainDeclarationRepositoryInterface $declarationRepository,
        private DomainBindingRepositoryInterface $bindingRepository,
        private DomainPublicationStateRepositoryInterface $publicationStateRepository,
        private DomainRoutingTargetRepositoryInterface $routingTargetRepository,
    ) {
    }

    public function forApplication(string $applicationKey, string $environment): DomainRuntimeOverlayDTO
    {
        $applicationKey = trim($applicationKey);
        $environment = trim($environment);
        if ('' === $applicationKey || '' === $environment) {
            throw new \InvalidArgumentException('Application key and environment must not be empty.');
        }

        $declaration = $this->declarationRepository->findPrimaryByApplication($applicationKey, $environment);

        if (null === $declaration) {
            return new DomainRuntimeOverlayDTO(
                $applicationKey,
                null,
                $environment,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                false,
                false,
                false,
            );
        }

        $binding = $this->bindingRepository->findOneForDeclaration($declaration);
        $publicationState = null === $binding ? null : $this->publicationStateRepository->findOneForBinding($binding);
        $routingTarget = null === $binding ? null : $this->routingTargetRepository->findOneForBinding($binding);

        return new DomainRuntimeOverlayDTO(
            $declaration->applicationKey(),
            $declaration->brandKey(),
            $declaration->environment(),
            $declaration->domainName(),
            $declaration->role()->value,
            $declaration->status()->value,
            $binding?->status()->value,
            $publicationState?->status()->value,
            $routingTarget?->targetHost(),
            $routingTarget?->targetPath(),
            true,
            in_array($declaration->status(), [DomainDeclarationStatus::Verified, DomainDeclarationStatus::Ready, DomainDeclarationStatus::Published], true),
            in_array($declaration->status(), [DomainDeclarationStatus::Ready, DomainDeclarationStatus::Published], true),
            DomainDeclarationStatus::Published === $declaration->status() && DomainPublicationStatus::Published === $publicationState?->status(),
        );
    }
}
