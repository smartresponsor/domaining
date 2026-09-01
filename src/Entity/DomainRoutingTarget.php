<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Repository\DomainRoutingTargetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainRoutingTargetRepository::class)]
#[ORM\Table(name: 'domain_routing_target')]
#[ORM\UniqueConstraint(name: 'uniq_domain_routing_target_binding', columns: ['binding_id'])]
class DomainRoutingTarget
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\OneToOne(targetEntity: DomainBinding::class)]
    #[ORM\JoinColumn(name: 'binding_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private DomainBinding $binding;

    #[ORM\Column(name: 'target_host', length: 253)]
    private string $targetHost;

    #[ORM\Column(name: 'target_path', length: 512)]
    private string $targetPath = '/';

    public function __construct(DomainBinding $binding, string $targetHost, string $targetPath = '/')
    {
        $this->id = Uuid::v7();
        $this->binding = $binding;
        $this->targetHost = $targetHost;
        $this->targetPath = $targetPath;
    }

    public function id(): Uuid { return $this->id; }
    public function binding(): DomainBinding { return $this->binding; }
    public function targetHost(): string { return $this->targetHost; }
    public function targetPath(): string { return $this->targetPath; }

    public function retarget(string $targetHost, string $targetPath = '/'): void
    {
        $this->targetHost = $targetHost;
        $this->targetPath = $targetPath;
    }
}

