<?php

declare(strict_types=1);

namespace App\Domaining\Service\Runtime;

use App\Domaining\Dto\DomainRuntimeOverlay;
use App\Domaining\Entity\DomainBinding;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainDeclarationRepository;
use App\Domaining\Repository\DomainPublicationStateRepository;
use App\Domaining\Repository\DomainRoutingTargetRepository;
use App\Domaining\ServiceInterface\Runtime\DomainRuntimeOverlayServiceInterface;

final readonly class DomainRuntimeOverlayService implements DomainRuntimeOverlayServiceInterface
{
    public function __construct(
        private DomainDeclarationRepository $declarationRepository,
        private DomainBindingRepository $bindingRepository,
        private DomainPublicationStateRepository $publicationStateRepository,
        private DomainRoutingTargetRepository $routingTargetRepository,
    ) {
    }

    public function forApplication(string $applicationKey, string $environment): DomainRuntimeOverlay
    {
        $applicationKey = trim($applicationKey);
        $environment = trim($environment);
        if ('' === $applicationKey || '' === $environment) {
            throw new \InvalidArgumentException('Application key and environment must not be empty.');
        }

        $declaration = $this->declarationRepository->findPrimaryByApplication($applicationKey, $environment);

        if (null === $declaration) {
            return new DomainRuntimeOverlay(
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

        $binding = $this->bindingRepository->findOneBy(['declaration' => $declaration]);
        $binding = $binding instanceof DomainBinding ? $binding : null;
        $publicationState = null === $binding ? null : $this->publicationStateRepository->findOneBy(['binding' => $binding]);
        $routingTarget = null === $binding ? null : $this->routingTargetRepository->findOneBy(['binding' => $binding]);

        return new DomainRuntimeOverlay(
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
