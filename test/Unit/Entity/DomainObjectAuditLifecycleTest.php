<?php

declare(strict_types=1);

namespace App\Domaining\Test\Unit\Entity;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainSurfaceType;
use PHPUnit\Framework\TestCase;

final class DomainObjectAuditLifecycleTest extends TestCase
{
    public function testBindingUsesObjectingAuditLifecycle(): void
    {
        $binding = new DomainBindingEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');

        self::assertNull($binding->getModifiedAt());

        $binding->activate();

        self::assertNotNull($binding->getModifiedAt());
    }

    public function testVerificationChallengeUsesObjectingAuditLifecycle(): void
    {
        $claim = new DomainClaimEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');
        $challenge = new DomainVerificationChallengeEntity(
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
