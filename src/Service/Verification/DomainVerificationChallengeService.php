<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Verification\DomainVerificationChallengeServiceInterface;
use App\Domaining\Value\DomainVerificationToken;

final readonly class DomainVerificationChallengeService implements DomainVerificationChallengeServiceInterface
{
    public function __construct(private DomainPersistenceRepository $persistenceRepository)
    {
    }

    public function issueTxtChallenge(DomainClaimEntity $claim): DomainVerificationChallengeEntity
    {
        if (DomainClaimStatus::Pending !== $claim->status()) {
            throw new \DomainException(sprintf('TXT verification challenge can only be issued for pending claims; current status is "%s".', $claim->status()->value));
        }

        $recordName = '_smartresponsor-domain.'.$claim->domainName();
        $recordValue = (string) DomainVerificationToken::create();
        $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P7D'));
        $challenge = new DomainVerificationChallengeEntity($claim, DomainRecordType::Txt, $recordName, $recordValue, $expiresAt);

        $claim->markChallengeIssued();
        $this->persistenceRepository->persist($challenge);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($claim->domainName(), 'domain_verification_challenge_issued', $claim->ownerId(), [
            'record_type' => DomainRecordType::Txt->value,
            'record_name' => $recordName,
        ]));
        $this->persistenceRepository->flush();

        return $challenge;
    }
}
