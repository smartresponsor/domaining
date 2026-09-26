<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Repository\DomainDeclarationRepository;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityInterface\ObjectIdentifiedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainDeclarationRepository::class)]
#[ORM\Table(name: 'domain_declaration')]
#[ORM\UniqueConstraint(name: 'domain_declaration_name_environment_unique', columns: ['domain_name', 'environment'])]
#[ORM\Index(name: 'domain_declaration_application_environment_idx', columns: ['application_key', 'environment'])]
#[ORM\Index(name: 'domain_declaration_status_idx', columns: ['status'])]
class DomainDeclarationEntity implements ObjectAuditedInterface, ObjectIdentifiedInterface
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(name: 'application_key', length: 128)]
    private string $applicationKey;

    #[ORM\Column(name: 'brand_key', length: 128)]
    private string $brandKey;

    #[ORM\Column(length: 32)]
    private string $environment;

    #[ORM\Column(name: 'domain_name', length: 253)]
    private string $domainName;

    #[ORM\Column(length: 32, enumType: DomainApplicationRole::class)]
    private DomainApplicationRole $role;

    #[ORM\Column(length: 32, enumType: DomainDeclarationStatus::class)]
    private DomainDeclarationStatus $status = DomainDeclarationStatus::Declared;

    public function __construct(
        string $applicationKey,
        string $brandKey,
        string $environment,
        string $domainName,
        DomainApplicationRole $role = DomainApplicationRole::Primary,
    ) {
        $this->id = Uuid::v7();
        $this->applicationKey = self::normalizeKey($applicationKey, 'Application key');
        $this->brandKey = self::normalizeKey($brandKey, 'Brand key');
        $this->environment = self::normalizeKey($environment, 'Environment');
        $this->domainName = self::normalizeDomainName($domainName);
        $this->role = $role;
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function applicationKey(): string
    {
        return $this->applicationKey;
    }

    public function getApplicationKey(): string
    {
        return $this->applicationKey;
    }

    public function brandKey(): string
    {
        return $this->brandKey;
    }

    public function getBrandKey(): string
    {
        return $this->brandKey;
    }

    public function environment(): string
    {
        return $this->environment;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function domainName(): string
    {
        return $this->domainName;
    }

    public function getDomainName(): string
    {
        return $this->domainName;
    }

    public function role(): DomainApplicationRole
    {
        return $this->role;
    }

    public function getRole(): DomainApplicationRole
    {
        return $this->role;
    }

    public function status(): DomainDeclarationStatus
    {
        return $this->status;
    }

    public function getStatus(): DomainDeclarationStatus
    {
        return $this->status;
    }

    public function setApplicationKey(string $applicationKey): self
    {
        $this->applicationKey = self::normalizeKey($applicationKey, 'Application key');
        $this->touch();

        return $this;
    }

    public function setBrandKey(string $brandKey): self
    {
        $this->brandKey = self::normalizeKey($brandKey, 'Brand key');
        $this->touch();

        return $this;
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = self::normalizeKey($environment, 'Environment');
        $this->touch();

        return $this;
    }

    public function setDomainName(string $domainName): self
    {
        $this->domainName = self::normalizeDomainName($domainName);
        $this->touch();

        return $this;
    }

    public function setRole(DomainApplicationRole $role): self
    {
        $this->role = $role;
        $this->touch();

        return $this;
    }

    public function redeclare(string $brandKey, DomainApplicationRole $role): void
    {
        $this->brandKey = self::normalizeKey($brandKey, 'Brand key');
        $this->role = $role;
        $this->touch();
    }

    public function markClaimPending(): void
    {
        $this->status = DomainDeclarationStatus::ClaimPending;
        $this->touch();
    }

    public function markVerified(): void
    {
        $this->status = DomainDeclarationStatus::Verified;
        $this->touch();
    }

    public function markReady(): void
    {
        $this->status = DomainDeclarationStatus::Ready;
        $this->touch();
    }

    public function markPublished(): void
    {
        $this->status = DomainDeclarationStatus::Published;
        $this->touch();
    }

    public function suspend(): void
    {
        $this->status = DomainDeclarationStatus::Suspended;
        $this->touch();
    }

    public function withdraw(): void
    {
        $this->status = DomainDeclarationStatus::Withdrawn;
        $this->touch();
    }

    private function touch(): void
    {
        $this->touchModified();
    }

    private static function normalizeKey(string $value, string $label): string
    {
        $value = trim($value);
        if ('' === $value) {
            throw new \InvalidArgumentException(sprintf('%s must not be empty.', $label));
        }

        return $value;
    }

    private static function normalizeDomainName(string $domainName): string
    {
        $domainName = strtolower(rtrim(trim($domainName), '.'));
        if ('' === $domainName || strlen($domainName) > 253 || false === filter_var($domainName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException(sprintf('Domain name "%s" is invalid.', $domainName));
        }

        return $domainName;
    }
}
