<?php

declare(strict_types=1);

namespace App\Domaining\Service\Security;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\ServiceInterface\Security\DomainOwnershipGuardServiceInterface;

final readonly class DomainOwnershipGuardService implements DomainOwnershipGuardServiceInterface
{
    /**
     * @param list<string> $reservedDomains
     * @param list<string> $allowedRoutingHosts
     */
    public function __construct(
        private DomainBindingRepository $bindingRepository,
        private array $reservedDomains = ['smart-responder.com', 'smartresponsor.com'],
        private array $allowedRoutingHosts = ['smart-responder.app', 'smartresponsor.app'],
    ) {
    }

    public function assertClaimCanBeCreated(DomainClaim $claim): void
    {
        $domainName = strtolower($claim->domainName());

        foreach ($this->reservedDomains as $reservedDomain) {
            if ($domainName === $reservedDomain || str_ends_with($domainName, '.'.$reservedDomain)) {
                throw DomainInvalidStateException::create(sprintf('Domain "%s" is reserved by the platform and cannot be claimed.', $claim->domainName()));
            }
        }

        $existing = $this->bindingRepository->findLiveByDomainName($claim->domainName());
        if ($existing instanceof DomainBinding && $existing->ownerId() !== $claim->ownerId()) {
            throw DomainConflictException::create(sprintf('Domain "%s" already has a live binding for another owner.', $claim->domainName()));
        }
    }

    public function assertBindingCanBeActivated(DomainBinding $binding): void
    {
        $existing = $this->bindingRepository->findLiveByDomainName($binding->domainName());
        if (!$existing instanceof DomainBinding) {
            return;
        }

        if ((string) $existing->id() !== (string) $binding->id() && $existing->ownerId() !== $binding->ownerId()) {
            throw DomainConflictException::create(sprintf('Domain "%s" cannot be activated because another live binding exists.', $binding->domainName()));
        }
    }

    public function assertRoutingTargetIsAllowed(string $targetHost, string $targetPath): void
    {
        if ('' === trim($targetHost) || str_contains($targetHost, '/')) {
            throw DomainInvalidStateException::create('Routing target host must be a hostname without path separators.');
        }

        if (!str_starts_with($targetPath, '/')) {
            throw DomainInvalidStateException::create('Routing target path must start with /.');
        }

        if ([] === $this->allowedRoutingHosts) {
            return;
        }

        foreach ($this->allowedRoutingHosts as $allowedHost) {
            if ($targetHost === $allowedHost || str_ends_with($targetHost, '.'.$allowedHost)) {
                return;
            }
        }

        throw DomainInvalidStateException::create(sprintf('Routing target host "%s" is not allowed by Domaining configuration.', $targetHost));
    }
}
