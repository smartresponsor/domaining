<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainClaimRepository;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainClaimRepository::class)]
#[ORM\Table(name: 'domain_claim')]
#[ORM\UniqueConstraint(name: 'domain_claim_name_owner_surface_unique', columns: ['domain_name', 'owner_id', 'surface_type', 'surface_key'])]
class DomainClaim implements ObjectAuditedInterface
{
    use ObjectAuditEmbeddableTrait;

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

    #[ORM\Column(length: 32, enumType: DomainClaimStatus::class)]
    private DomainClaimStatus $status = DomainClaimStatus::Pending;

    public function __construct(string $domainName, string $ownerId, DomainSurfaceType $surfaceType, string $surfaceKey, ?DomainDeclaration $declaration = null)
    {
        $now = new \DateTimeImmutable();
        $this->id = Uuid::v7();
        $this->domainName = $domainName;
        $this->ownerId = $ownerId;
        $this->surfaceType = $surfaceType;
        $this->surfaceKey = $surfaceKey;
        $this->declaration = $declaration;
        $this->initializeObjectAudit($now);
    }

    public function id(): Uuid { return $this->id; }
    public function domainName(): string { return $this->domainName; }
    public function ownerId(): string { return $this->ownerId; }
    public function surfaceType(): DomainSurfaceType { return $this->surfaceType; }
    public function surfaceKey(): string { return $this->surfaceKey; }
    public function status(): DomainClaimStatus { return $this->status; }
    public function declaration(): ?DomainDeclaration { return $this->declaration; }

    public function markChallengeIssued(): void
    {
        $this->status = DomainClaimStatus::ChallengeIssued;
        $this->touch();
    }

    public function markVerified(): void
    {
        $this->status = DomainClaimStatus::Verified;
        $this->touch();
    }

    public function cancel(): void
    {
        $this->status = DomainClaimStatus::Cancelled;
        $this->touch();
    }

    private function touch(): void
    {
        $this->touchModified();
    }
}

