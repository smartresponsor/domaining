<?php

declare(strict_types=1);

namespace App\Domaining\Entity;

use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainVerificationChallengeRepository::class)]
#[ORM\Table(name: 'domain_verification_challenge')]
#[ORM\Index(name: 'idx_domain_verification_challenge_claim', columns: ['claim_id'])]
#[ORM\Index(name: 'domain_verification_ready_idx', columns: ['status', 'expires_at', 'next_check_after'])]
#[ORM\Index(name: 'domain_verification_challenge_status_retry_idx', columns: ['status', 'next_check_after'])]
class DomainVerificationChallengeEntity implements ObjectAuditedInterface
{
    use ObjectAuditEmbeddableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: DomainClaimEntity::class)]
    #[ORM\JoinColumn(name: 'claim_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private DomainClaimEntity $claim;

    #[ORM\Column(name: 'record_type', length: 16, enumType: DomainRecordType::class)]
    private DomainRecordType $recordType;

    #[ORM\Column(name: 'record_name', length: 253)]
    private string $recordName;

    #[ORM\Column(name: 'record_value', length: 512)]
    private string $recordValue;

    #[ORM\Column(length: 32, enumType: DomainVerificationStatus::class)]
    private DomainVerificationStatus $status = DomainVerificationStatus::Pending;

    #[ORM\Column(name: 'expires_at')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'verified_at', nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(name: 'checked_at', nullable: true)]
    private ?\DateTimeImmutable $checkedAt = null;

    #[ORM\Column(name: 'next_check_after', nullable: true)]
    private ?\DateTimeImmutable $nextCheckAfter = null;

    #[ORM\Column(name: 'attempt_count')]
    private int $attemptCount = 0;

    #[ORM\Column(name: 'last_failure_reason', length: 512, nullable: true)]
    private ?string $lastFailureReason = null;

    public function __construct(DomainClaimEntity $claim, DomainRecordType $recordType, string $recordName, string $recordValue, \DateTimeImmutable $expiresAt)
    {
        $this->id = Uuid::v7();
        $this->claim = $claim;
        $this->recordType = $recordType;
        $this->recordName = $recordName;
        $this->recordValue = $recordValue;
        $this->expiresAt = $expiresAt;
        $this->initializeObjectAudit();
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function claim(): DomainClaimEntity
    {
        return $this->claim;
    }

    public function recordType(): DomainRecordType
    {
        return $this->recordType;
    }

    public function recordName(): string
    {
        return $this->recordName;
    }

    public function recordValue(): string
    {
        return $this->recordValue;
    }

    public function status(): DomainVerificationStatus
    {
        return $this->status;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function verifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function attemptCount(): int
    {
        return $this->attemptCount;
    }

    public function checkedAt(): ?\DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function nextCheckAfter(): ?\DateTimeImmutable
    {
        return $this->nextCheckAfter;
    }

    public function lastFailureReason(): ?string
    {
        return $this->lastFailureReason;
    }

    public function markChecked(?string $failureReason = null, int $retryDelaySeconds = 300): void
    {
        $now = new \DateTimeImmutable();
        $this->checkedAt = $now;
        $this->nextCheckAfter = $now->modify(sprintf('+%d seconds', max(60, $retryDelaySeconds)));
        ++$this->attemptCount;
        $this->lastFailureReason = $failureReason;
        $this->touchModified($now);
    }

    public function markPassed(): void
    {
        $now = new \DateTimeImmutable();
        $this->status = DomainVerificationStatus::Passed;
        $this->verifiedAt = $now;
        $this->checkedAt = $now;
        $this->nextCheckAfter = null;
        $this->lastFailureReason = null;
        $this->touchModified($now);
    }

    public function markFailed(?string $reason = null): void
    {
        $this->status = DomainVerificationStatus::Failed;
        $this->markChecked($reason);
    }

    public function markExpired(): void
    {
        $this->status = DomainVerificationStatus::Expired;
        $this->lastFailureReason = 'Verification challenge has expired.';
        $this->touchModified();
    }

    public function canBeChecked(\DateTimeImmutable $now): bool
    {
        return null === $this->nextCheckAfter || $this->nextCheckAfter <= $now;
    }

    public function expired(\DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }
}
