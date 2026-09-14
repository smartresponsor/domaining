<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainBindingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainBindingRepository::class)]
#[ORM\Table(name: 'domain_binding')]
#[ORM\UniqueConstraint(name: 'domain_binding_name_active_unique', columns: ['domain_name'])]
#[ORM\Index(name: 'domain_binding_name_idx', columns: ['domain_name'])]
#[ORM\Index(name: 'domain_binding_owner_surface_idx', columns: ['owner_id', 'surface_type', 'surface_key'])]
class DomainBinding
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(name: 'domain_name', length: 253)]
    private string $domainName;

    #[ORM\ManyToOne(targetEntity: DomainDeclaration::class)]
    #[ORM\JoinColumn(name: 'declaration_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?DomainDeclaration $declaration = null;

    #[ORM\Column(name: 'owner_id', length: 128)]
    private string $ownerId;

    #[ORM\Column(name: 'surface_type', length: 32, enumType: DomainSurfaceType::class)]
    private DomainSurfaceType $surfaceType;

    #[ORM\Column(name: 'surface_key', length: 128)]
    private string $surfaceKey;

    #[ORM\Column(length: 32, enumType: DomainBindingStatus::class)]
    private DomainBindingStatus $status = DomainBindingStatus::Verified;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'activated_at', nullable: true)]
    private ?DateTimeImmutable $activatedAt = null;

    #[ORM\Column(name: 'suspended_at', nullable: true)]
    private ?DateTimeImmutable $suspendedAt = null;

    #[ORM\Column(name: 'removed_at', nullable: true)]
    private ?DateTimeImmutable $removedAt = null;

    #[ORM\Column(name: 'last_verified_at', nullable: true)]
    private ?DateTimeImmutable $lastVerifiedAt = null;

    public function __construct(string $domainName, string $ownerId, DomainSurfaceType $surfaceType, string $surfaceKey, ?DomainDeclaration $declaration = null)
    {
        $this->id = Uuid::v7();
        $this->domainName = $domainName;
        $this->ownerId = $ownerId;
        $this->surfaceType = $surfaceType;
        $this->surfaceKey = $surfaceKey;
        $this->declaration = $declaration;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): Uuid { return $this->id; }
    public function domainName(): string { return $this->domainName; }
    public function ownerId(): string { return $this->ownerId; }
    public function surfaceType(): DomainSurfaceType { return $this->surfaceType; }
    public function surfaceKey(): string { return $this->surfaceKey; }
    public function declaration(): ?DomainDeclaration { return $this->declaration; }
    public function status(): DomainBindingStatus { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function activatedAt(): ?DateTimeImmutable { return $this->activatedAt; }
    public function suspendedAt(): ?DateTimeImmutable { return $this->suspendedAt; }
    public function removedAt(): ?DateTimeImmutable { return $this->removedAt; }
    public function lastVerifiedAt(): ?DateTimeImmutable { return $this->lastVerifiedAt; }

    public function markVerifiedNow(): void
    {
        $this->lastVerifiedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = DomainBindingStatus::Active;
        $this->activatedAt = new DateTimeImmutable();
    }

    public function suspend(): void
    {
        $this->status = DomainBindingStatus::Suspended;
        $this->suspendedAt = new DateTimeImmutable();
    }

    public function remove(): void
    {
        $this->status = DomainBindingStatus::Removed;
        $this->removedAt = new DateTimeImmutable();
    }
}

