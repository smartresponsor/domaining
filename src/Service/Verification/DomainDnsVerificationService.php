<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\Dto\DomainVerificationResult;
use App\Domaining\Entity\DomainAuditRecord;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\ServiceInterface\Verification\DomainDnsVerificationServiceInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DomainDnsVerificationService implements DomainDnsVerificationServiceInterface
{
    private const RETRY_DELAY_SECONDS = 300;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function verify(DomainVerificationChallenge $challenge): DomainVerificationResult
    {
        return $this->check($challenge, false);
    }

    public function recheck(DomainVerificationChallenge $challenge): DomainVerificationResult
    {
        return $this->check($challenge, true);
    }

    private function check(DomainVerificationChallenge $challenge, bool $force): DomainVerificationResult
    {
        $now = new DateTimeImmutable();
        if ($challenge->expired($now)) {
            $challenge->markExpired();
            $this->entityManager->persist(new DomainAuditRecord($challenge->claim()->domainName(), 'domain_verification_expired', $challenge->claim()->ownerId(), [
                'record_name' => $challenge->recordName(),
            ]));
            $this->entityManager->flush();

            return new DomainVerificationResult(DomainVerificationStatus::Expired, 'Verification challenge has expired.');
        }

        if (!$force && !$challenge->canBeChecked($now)) {
            return new DomainVerificationResult(DomainVerificationStatus::Pending, 'Verification was checked recently. Retry after the next check window.');
        }

        if (DomainRecordType::Txt !== $challenge->recordType()) {
            $challenge->markChecked('Only TXT verification is enabled for automated checks.', self::RETRY_DELAY_SECONDS);
            $this->entityManager->flush();

            return new DomainVerificationResult(DomainVerificationStatus::Failed, 'Only TXT verification is enabled for automated checks.');
        }

        $records = dns_get_record($challenge->recordName(), DNS_TXT) ?: [];
        foreach ($records as $record) {
            $value = (string) ($record['txt'] ?? '');
            if (hash_equals($challenge->recordValue(), $value)) {
                $challenge->markPassed();
                $challenge->claim()->markVerified();
                $challenge->claim()->declaration()?->markVerified();
                $this->entityManager->persist(new DomainAuditRecord($challenge->claim()->domainName(), 'domain_verification_passed', $challenge->claim()->ownerId(), [
                    'record_name' => $challenge->recordName(),
                    'attempt_count' => $challenge->attemptCount(),
                ]));
                $this->entityManager->flush();

                return new DomainVerificationResult(DomainVerificationStatus::Passed, 'Domain ownership has been verified.', $record);
            }
        }

        $challenge->markChecked('Expected TXT verification record was not found.', self::RETRY_DELAY_SECONDS);
        $this->entityManager->persist(new DomainAuditRecord($challenge->claim()->domainName(), 'domain_verification_pending', $challenge->claim()->ownerId(), [
            'record_name' => $challenge->recordName(),
            'attempt_count' => $challenge->attemptCount(),
        ]));
        $this->entityManager->flush();

        return new DomainVerificationResult(DomainVerificationStatus::Pending, 'Expected TXT verification record was not found yet. Keep the DNS record in place and retry after propagation.');
    }
}
