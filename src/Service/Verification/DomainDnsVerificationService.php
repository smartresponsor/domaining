<?php

declare(strict_types=1);

namespace App\Domaining\Service\Verification;

use App\Domaining\DTO\DomainVerificationResultDTO;
use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainPersistenceRepository;
use App\Domaining\ServiceInterface\Verification\DomainDnsVerificationServiceInterface;

final readonly class DomainDnsVerificationService implements DomainDnsVerificationServiceInterface
{
    private const RETRY_DELAY_SECONDS = 300;

    public function __construct(private DomainPersistenceRepository $persistenceRepository)
    {
    }

    public function verify(DomainVerificationChallengeEntity $challenge): DomainVerificationResultDTO
    {
        return $this->check($challenge, false);
    }

    public function recheck(DomainVerificationChallengeEntity $challenge): DomainVerificationResultDTO
    {
        return $this->check($challenge, true);
    }

    private function check(DomainVerificationChallengeEntity $challenge, bool $force): DomainVerificationResultDTO
    {
        $now = new \DateTimeImmutable();
        if ($challenge->expired($now)) {
            $challenge->markExpired();
            $this->persistenceRepository->persist(new DomainAuditRecordEntity($challenge->claim()->domainName(), 'domain_verification_expired', $challenge->claim()->ownerId(), [
                'record_name' => $challenge->recordName(),
            ]));
            $this->persistenceRepository->flush();

            return new DomainVerificationResultDTO(DomainVerificationStatus::Expired, 'Verification challenge has expired.');
        }

        if (!$force && !$challenge->canBeChecked($now)) {
            return new DomainVerificationResultDTO(DomainVerificationStatus::Pending, 'Verification was checked recently. Retry after the next check window.');
        }

        if (DomainRecordType::Txt !== $challenge->recordType()) {
            $challenge->markChecked('Only TXT verification is enabled for automated checks.', self::RETRY_DELAY_SECONDS);
            $this->persistenceRepository->flush();

            return new DomainVerificationResultDTO(DomainVerificationStatus::Failed, 'Only TXT verification is enabled for automated checks.');
        }

        $records = dns_get_record($challenge->recordName(), DNS_TXT) ?: [];
        foreach ($records as $record) {
            $value = (string) ($record['txt'] ?? '');
            if (hash_equals($challenge->recordValue(), $value)) {
                $challenge->markPassed();
                $challenge->claim()->markVerified();
                $challenge->claim()->declaration()?->markVerified();
                $this->persistenceRepository->persist(new DomainAuditRecordEntity($challenge->claim()->domainName(), 'domain_verification_passed', $challenge->claim()->ownerId(), [
                    'record_name' => $challenge->recordName(),
                    'attempt_count' => $challenge->attemptCount(),
                ]));
                $this->persistenceRepository->flush();

                return new DomainVerificationResultDTO(DomainVerificationStatus::Passed, 'Domain ownership has been verified.', $record);
            }
        }

        $challenge->markChecked('Expected TXT verification record was not found.', self::RETRY_DELAY_SECONDS);
        $this->persistenceRepository->persist(new DomainAuditRecordEntity($challenge->claim()->domainName(), 'domain_verification_pending', $challenge->claim()->ownerId(), [
            'record_name' => $challenge->recordName(),
            'attempt_count' => $challenge->attemptCount(),
        ]));
        $this->persistenceRepository->flush();

        return new DomainVerificationResultDTO(DomainVerificationStatus::Pending, 'Expected TXT verification record was not found yet. Keep the DNS record in place and retry after propagation.');
    }
}
