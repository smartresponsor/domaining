<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\ServiceInterface\Verification\DomainVerificationChallengeServiceInterface;
use App\Domaining\Value\DomainVerificationToken;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainVerificationChallengeService implements DomainVerificationChallengeServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function issueTxtChallenge(DomainClaim $claim): DomainVerificationChallenge
    {
        if (DomainClaimStatus::Pending !== $claim->status()) {
            throw new \DomainException(sprintf(
                'TXT verification challenge can only be issued for pending claims; current status is "%s".',
                $claim->status()->value,
            ));
        }

        $recordName = '_smartresponsor-domain.' . $claim->domainName();
        $recordValue = (string) DomainVerificationToken::create();
        $expiresAt = (new DateTimeImmutable())->add(new DateInterval('P7D'));
        $challenge = new DomainVerificationChallenge($claim, DomainRecordType::Txt, $recordName, $recordValue, $expiresAt);

        $claim->markChallengeIssued();
        $this->entityManager->persist($challenge);
        $this->entityManager->persist(new DomainAuditRecord($claim->domainName(), 'domain_verification_challenge_issued', $claim->ownerId(), [
            'record_type' => DomainRecordType::Txt->value,
            'record_name' => $recordName,
        ]));
        $this->entityManager->flush();

        return $challenge;
    }
}
