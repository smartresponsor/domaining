<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Repository\DomainAuditRecordRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainAuditRecordRepository::class)]
#[ORM\Table(name: 'domain_audit_record')]
#[ORM\Index(name: 'domain_audit_record_domain_created_idx', columns: ['domain_name', 'created_at'])]
#[ORM\Index(name: 'domain_audit_record_action_created_idx', columns: ['action', 'created_at'])]
class DomainAuditRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(name: 'domain_name', length: 253)]
    private string $domainName;

    #[ORM\Column(length: 96)]
    private string $action;

    #[ORM\Column(name: 'actor_id', length: 128, nullable: true)]
    private ?string $actorId;

    #[ORM\Column(type: 'json')]
    private array $context;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $domainName, string $action, ?string $actorId = null, array $context = [])
    {
        $this->id = Uuid::v7();
        $this->domainName = $domainName;
        $this->action = $action;
        $this->actorId = $actorId;
        $this->context = $context;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): Uuid { return $this->id; }
    public function domainName(): string { return $this->domainName; }
    public function action(): string { return $this->action; }
    public function actorId(): ?string { return $this->actorId; }

    /** @return array<string, mixed> */
    public function context(): array { return $this->context; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
}

