<?php

declare(strict_types=1);

namespace App\Domaining\Test\Unit\Entity;

use App\Domaining\Entity\DomainBinding;
use App\Domaining\Entity\DomainClaim;
use App\Domaining\Entity\DomainVerificationChallenge;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainSurfaceType;
use PHPUnit\Framework\TestCase;

final class DomainObjectAuditLifecycleTest extends TestCase
{
    public function testBindingUsesObjectingAuditLifecycle(): void
    {
        $binding = new DomainBinding('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');

        self::assertNull($binding->getModifiedAt());

        $binding->activate();

        self::assertNotNull($binding->getModifiedAt());
    }

    public function testVerificationChallengeUsesObjectingAuditLifecycle(): void
    {
        $claim = new DomainClaim('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');
        $challenge = new DomainVerificationChallenge(
            $claim,
            DomainRecordType::Txt,
            '_smartresponsor-domain.example.com',
            'sr-domain-verification=token',
            new \DateTimeImmutable('+1 hour'),
        );

        self::assertNull($challenge->getModifiedAt());

        $challenge->markChecked('not propagated', 60);

        self::assertNotNull($challenge->getModifiedAt());
    }
}
