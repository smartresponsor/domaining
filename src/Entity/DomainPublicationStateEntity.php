<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Repository\DomainPublicationStateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainPublicationStateRepository::class)]
#[ORM\Table(name: 'domain_publication_state')]
#[ORM\UniqueConstraint(name: 'uniq_domain_publication_state_binding', columns: ['binding_id'])]
#[ORM\Index(name: 'domain_publication_state_status_idx', columns: ['status'])]
#[ORM\Index(name: 'domain_publication_state_status_updated_idx', columns: ['status', 'ready_at'])]
class DomainPublicationStateEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\OneToOne(targetEntity: DomainBindingEntity::class)]
    #[ORM\JoinColumn(name: 'binding_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private DomainBindingEntity $binding;

    #[ORM\Column(length: 32, enumType: DomainPublicationStatus::class)]
    private DomainPublicationStatus $status = DomainPublicationStatus::NotReady;

    #[ORM\Column(name: 'ready_at', nullable: true)]
    private ?\DateTimeImmutable $readyAt = null;

    #[ORM\Column(name: 'published_at', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(name: 'withdrawn_at', nullable: true)]
    private ?\DateTimeImmutable $withdrawnAt = null;

    public function __construct(DomainBindingEntity $binding)
    {
        $this->id = Uuid::v7();
        $this->binding = $binding;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function binding(): DomainBindingEntity
    {
        return $this->binding;
    }

    public function status(): DomainPublicationStatus
    {
        return $this->status;
    }

    public function readyAt(): ?\DateTimeImmutable
    {
        return $this->readyAt;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function withdrawnAt(): ?\DateTimeImmutable
    {
        return $this->withdrawnAt;
    }

    public function markReady(): void
    {
        $this->status = DomainPublicationStatus::Ready;
        $this->readyAt = new \DateTimeImmutable();
        $this->withdrawnAt = null;
    }

    public function markPublished(): void
    {
        $this->status = DomainPublicationStatus::Published;
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function markWithdrawn(): void
    {
        $this->status = DomainPublicationStatus::Withdrawn;
        $this->withdrawnAt = new \DateTimeImmutable();
    }
}
