<?php

declare(strict_types=1);

namespace App\Domaining\Test\Unit;

use App\Domaining\Entity\DomainAuditRecordEntity;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainDeclarationEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Enum\DomainBindingStatus;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainDeclarationStatus;
use App\Domaining\Enum\DomainLifecycleTransition;
use App\Domaining\Enum\DomainPublicationStatus;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Exception\DomainConflictException;
use App\Domaining\Exception\DomainInvalidStateException;
use App\Domaining\Service\State\DomainBindingTransitionGuard;
use App\Domaining\Value\DomainName;
use App\Domaining\Value\DomainVerificationToken;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DomainCoreLifecycleTest extends TestCase
{
    public function testDomainNameNormalizesValidInput(): void
    {
        $domain = new DomainName('  WWW.Example.COM. ');

        self::assertSame('www.example.com', $domain->value);
        self::assertSame('www.example.com', (string) $domain);
        self::assertSame('example.com', DomainName::normalize('.Example.COM.'));
        self::assertTrue(DomainName::isValid('sub.example.com'));
    }

    #[DataProvider('invalidDomainProvider')]
    public function testDomainNameRejectsInvalidInput(string $domain): void
    {
        self::assertFalse(DomainName::isValid(DomainName::normalize($domain)));

        $this->expectException(InvalidArgumentException::class);
        new DomainName($domain);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidDomainProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'single-label' => ['localhost'];
        yield 'leading-hyphen' => ['-bad.example'];
        yield 'too-long' => [str_repeat('a', 254)];
    }

    public function testVerificationTokenHasExpectedEntropyShape(): void
    {
        $first = DomainVerificationToken::create();
        $second = DomainVerificationToken::create();

        self::assertMatchesRegularExpression('/^sr-domain-verification=[a-f0-9]{48}$/', $first->value);
        self::assertSame($first->value, (string) $first);
        self::assertNotSame($first->value, $second->value);
    }

    public function testClaimLifecycleAndAuditSurface(): void
    {
        $claim = new DomainClaimEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');

        self::assertSame('example.com', $claim->domainName());
        self::assertSame('vendor-1', $claim->ownerId());
        self::assertSame(DomainSurfaceType::Application, $claim->surfaceType());
        self::assertSame('main', $claim->surfaceKey());
        self::assertSame(DomainClaimStatus::Pending, $claim->status());
        self::assertNull($claim->declaration());
        self::assertNull($claim->getModifiedAt());

        $claim->markChallengeIssued();
        self::assertSame(DomainClaimStatus::ChallengeIssued, $claim->status());
        self::assertNotNull($claim->getModifiedAt());

        $claim->markVerified();
        self::assertSame(DomainClaimStatus::Verified, $claim->status());

        $claim->cancel();
        self::assertSame(DomainClaimStatus::Cancelled, $claim->status());
        self::assertNotSame('', (string) $claim->id());
    }

    public function testBindingLifecycleTracksBusinessTimestamps(): void
    {
        $binding = new DomainBindingEntity('example.com', 'vendor-1', DomainSurfaceType::Storefront, 'shop');

        self::assertSame(DomainBindingStatus::Verified, $binding->status());
        self::assertNull($binding->activatedAt());
        self::assertNull($binding->suspendedAt());
        self::assertNull($binding->removedAt());
        self::assertNull($binding->lastVerifiedAt());
        self::assertNull($binding->declaration());

        $binding->markVerifiedNow();
        self::assertNotNull($binding->lastVerifiedAt());

        $binding->activate();
        self::assertSame(DomainBindingStatus::Active, $binding->status());
        self::assertNotNull($binding->activatedAt());

        $binding->suspend();
        self::assertSame(DomainBindingStatus::Suspended, $binding->status());
        self::assertNotNull($binding->suspendedAt());

        $binding->remove();
        self::assertSame(DomainBindingStatus::Removed, $binding->status());
        self::assertNotNull($binding->removedAt());
        self::assertNotNull($binding->getModifiedAt());
        self::assertSame('example.com', $binding->domainName());
        self::assertSame('vendor-1', $binding->ownerId());
        self::assertSame(DomainSurfaceType::Storefront, $binding->surfaceType());
        self::assertSame('shop', $binding->surfaceKey());
        self::assertNotSame('', (string) $binding->id());
    }

    public function testVerificationChallengeLifecycleAndRetryWindow(): void
    {
        $claim = new DomainClaimEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');
        $expiresAt = new DateTimeImmutable('+1 hour');
        $challenge = new DomainVerificationChallengeEntity(
            $claim,
            DomainRecordType::Txt,
            '_smartresponsor-domain.example.com',
            'sr-domain-verification=token',
            $expiresAt,
        );

        self::assertSame($claim, $challenge->claim());
        self::assertSame(DomainRecordType::Txt, $challenge->recordType());
        self::assertSame('_smartresponsor-domain.example.com', $challenge->recordName());
        self::assertSame('sr-domain-verification=token', $challenge->recordValue());
        self::assertSame(DomainVerificationStatus::Pending, $challenge->status());
        self::assertSame($expiresAt, $challenge->expiresAt());
        self::assertNull($challenge->verifiedAt());
        self::assertNull($challenge->checkedAt());
        self::assertNull($challenge->nextCheckAfter());
        self::assertNull($challenge->lastFailureReason());
        self::assertSame(0, $challenge->attemptCount());
        self::assertTrue($challenge->canBeChecked(new DateTimeImmutable()));
        self::assertFalse($challenge->expired(new DateTimeImmutable()));

        $challenge->markChecked('not propagated', 1);
        self::assertSame(1, $challenge->attemptCount());
        self::assertSame('not propagated', $challenge->lastFailureReason());
        self::assertNotNull($challenge->checkedAt());
        self::assertNotNull($challenge->nextCheckAfter());
        self::assertFalse($challenge->canBeChecked(new DateTimeImmutable()));

        $challenge->markFailed('provider error');
        self::assertSame(DomainVerificationStatus::Failed, $challenge->status());
        self::assertSame(2, $challenge->attemptCount());
        self::assertSame('provider error', $challenge->lastFailureReason());

        $challenge->markPassed();
        self::assertSame(DomainVerificationStatus::Passed, $challenge->status());
        self::assertNotNull($challenge->verifiedAt());
        self::assertNull($challenge->nextCheckAfter());
        self::assertNull($challenge->lastFailureReason());

        $challenge->markExpired();
        self::assertSame(DomainVerificationStatus::Expired, $challenge->status());
        self::assertSame('Verification challenge has expired.', $challenge->lastFailureReason());
        self::assertTrue($challenge->expired(new DateTimeImmutable('+2 hours')));
        self::assertNotSame('', (string) $challenge->id());
    }

    public function testPublicationAndRoutingStateTransitions(): void
    {
        $binding = new DomainBindingEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');
        $publication = new DomainPublicationStateEntity($binding);

        self::assertSame($binding, $publication->binding());
        self::assertSame(DomainPublicationStatus::NotReady, $publication->status());
        self::assertNull($publication->readyAt());
        self::assertNull($publication->publishedAt());
        self::assertNull($publication->withdrawnAt());

        $publication->markReady();
        self::assertSame(DomainPublicationStatus::Ready, $publication->status());
        self::assertNotNull($publication->readyAt());

        $publication->markPublished();
        self::assertSame(DomainPublicationStatus::Published, $publication->status());
        self::assertNotNull($publication->publishedAt());

        $publication->markWithdrawn();
        self::assertSame(DomainPublicationStatus::Withdrawn, $publication->status());
        self::assertNotNull($publication->withdrawnAt());
        self::assertNotSame('', (string) $publication->id());

        $target = new DomainRoutingTargetEntity($binding, 'tenant.smartresponsor.app');
        self::assertSame($binding, $target->binding());
        self::assertSame('tenant.smartresponsor.app', $target->targetHost());
        self::assertSame('/', $target->targetPath());

        $target->retarget('new.smartresponsor.app', '/site');
        self::assertSame('new.smartresponsor.app', $target->targetHost());
        self::assertSame('/site', $target->targetPath());
        self::assertNotSame('', (string) $target->id());
    }

    public function testAuditRecordRetainsBusinessEventContext(): void
    {
        $record = new DomainAuditRecordEntity('example.com', 'domain_tested', 'vendor-1', ['source' => 'unit']);

        self::assertSame('example.com', $record->domainName());
        self::assertSame('domain_tested', $record->action());
        self::assertSame('vendor-1', $record->actorId());
        self::assertSame(['source' => 'unit'], $record->context());
        self::assertInstanceOf(DateTimeImmutable::class, $record->createdAt());
        self::assertNotSame('', (string) $record->id());

        $anonymous = new DomainAuditRecordEntity('example.com', 'domain_tested');
        self::assertNull($anonymous->actorId());
        self::assertSame([], $anonymous->context());
    }

    public function testDeclarationNormalizesAndAdvancesLifecycle(): void
    {
        $declaration = new DomainDeclarationEntity(
            ' app ',
            ' brand ',
            ' production ',
            'WWW.Example.COM.',
            DomainApplicationRole::Primary,
        );

        self::assertSame($declaration->id(), $declaration->getId());
        self::assertSame('app', $declaration->applicationKey());
        self::assertSame('app', $declaration->getApplicationKey());
        self::assertSame('brand', $declaration->brandKey());
        self::assertSame('brand', $declaration->getBrandKey());
        self::assertSame('production', $declaration->environment());
        self::assertSame('production', $declaration->getEnvironment());
        self::assertSame('www.example.com', $declaration->domainName());
        self::assertSame('www.example.com', $declaration->getDomainName());
        self::assertSame(DomainApplicationRole::Primary, $declaration->role());
        self::assertSame(DomainApplicationRole::Primary, $declaration->getRole());
        self::assertSame(DomainDeclarationStatus::Declared, $declaration->status());
        self::assertSame(DomainDeclarationStatus::Declared, $declaration->getStatus());

        self::assertSame($declaration, $declaration->setApplicationKey('app-2'));
        self::assertSame($declaration, $declaration->setBrandKey('brand-2'));
        self::assertSame($declaration, $declaration->setEnvironment('staging'));
        self::assertSame($declaration, $declaration->setDomainName('api.example.com.'));
        self::assertSame($declaration, $declaration->setRole(DomainApplicationRole::Alias));
        $declaration->redeclare('brand-3', DomainApplicationRole::Primary);

        self::assertSame('app-2', $declaration->applicationKey());
        self::assertSame('brand-3', $declaration->brandKey());
        self::assertSame('staging', $declaration->environment());
        self::assertSame('api.example.com', $declaration->domainName());
        self::assertSame(DomainApplicationRole::Primary, $declaration->role());
        self::assertNotNull($declaration->getModifiedAt());

        $declaration->markClaimPending();
        self::assertSame(DomainDeclarationStatus::ClaimPending, $declaration->status());
        $declaration->markVerified();
        self::assertSame(DomainDeclarationStatus::Verified, $declaration->status());
        $declaration->markReady();
        self::assertSame(DomainDeclarationStatus::Ready, $declaration->status());
        $declaration->markPublished();
        self::assertSame(DomainDeclarationStatus::Published, $declaration->status());
        $declaration->suspend();
        self::assertSame(DomainDeclarationStatus::Suspended, $declaration->status());
        $declaration->withdraw();
        self::assertSame(DomainDeclarationStatus::Withdrawn, $declaration->status());
    }

    #[DataProvider('invalidDeclarationProvider')]
    public function testDeclarationRejectsInvalidFields(
        string $applicationKey,
        string $brandKey,
        string $environment,
        string $domainName,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        new DomainDeclarationEntity($applicationKey, $brandKey, $environment, $domainName);
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function invalidDeclarationProvider(): iterable
    {
        yield 'application' => ['', 'brand', 'production', 'example.com'];
        yield 'brand' => ['app', '', 'production', 'example.com'];
        yield 'environment' => ['app', 'brand', '', 'example.com'];
        yield 'domain' => ['app', 'brand', 'production', 'not a domain'];
    }

    public function testTransitionGuardAllowsCanonicalTransitions(): void
    {
        $guard = new DomainBindingTransitionGuard();
        $verified = $this->binding();
        $active = $this->binding();
        $active->activate();
        $suspended = $this->binding();
        $suspended->suspend();
        $removed = $this->binding();
        $removed->remove();

        self::assertTrue($guard->isAllowed($verified, DomainLifecycleTransition::ActivateBinding));
        self::assertTrue($guard->isAllowed($verified, DomainLifecycleTransition::SuspendBinding));
        self::assertTrue($guard->isAllowed($verified, DomainLifecycleTransition::RemoveBinding));
        self::assertTrue($guard->isAllowed($verified, DomainLifecycleTransition::PreparePublication));
        self::assertTrue($guard->isAllowed($active, DomainLifecycleTransition::SuspendBinding));
        self::assertTrue($guard->isAllowed($active, DomainLifecycleTransition::PreparePublication));
        self::assertTrue($guard->isAllowed($active, DomainLifecycleTransition::MarkPublished));
        self::assertTrue($guard->isAllowed($suspended, DomainLifecycleTransition::ActivateBinding));
        self::assertTrue($guard->isAllowed($suspended, DomainLifecycleTransition::RemoveBinding));
        self::assertTrue($guard->isAllowed($suspended, DomainLifecycleTransition::WithdrawPublication));
        self::assertTrue($guard->isAllowed($removed, DomainLifecycleTransition::WithdrawPublication));
        $guard->assertAllowed($active, DomainLifecycleTransition::MarkPublished);
        self::addToAssertionCount(1);
    }

    public function testTransitionGuardRejectsInvalidTransition(): void
    {
        $guard = new DomainBindingTransitionGuard();
        $binding = $this->binding();

        self::assertFalse($guard->isAllowed($binding, DomainLifecycleTransition::MarkPublished));

        $this->expectException(DomainInvalidStateException::class);
        $this->expectExceptionMessage('mark_published');
        $guard->assertAllowed($binding, DomainLifecycleTransition::MarkPublished);
    }

    public function testDomainExceptionsProvideDefaultsAndCustomMessages(): void
    {
        self::assertSame('Domain binding conflict detected.', DomainConflictException::create()->getMessage());
        self::assertSame('conflict', DomainConflictException::create('conflict')->getMessage());
        self::assertSame('Domain lifecycle transition is not allowed.', DomainInvalidStateException::create()->getMessage());
        self::assertSame('invalid', DomainInvalidStateException::create('invalid')->getMessage());
    }

    public function testSupportDtosEventsAndLegacyLifecyclePolicies(): void
    {
        $contractIssue = new \App\Domaining\DTO\DomainContractGovernanceIssueDTO('warning', 'contract', 'Review contract.', ['v' => 1]);
        self::assertSame('contract', $contractIssue->toArray()['code']);

        $policyIssue = new \App\Domaining\DTO\DomainSurfacePolicyIssueDTO('error', 'surface', 'Review surface.', ['owner' => 'vendor']);
        self::assertSame('surface', $policyIssue->toArray()['code']);

        $render = new \App\Domaining\DTO\DomainTemplateRenderResultDTO(true, 'domain', 'domain.html.twig', ['domainName' => 'example.com'], '<p>ok</p>');
        self::assertSame('<p>ok</p>', $render->toArray()['html']);

        $emptyOverlay = new \App\Domaining\DTO\DomainRuntimeOverlayDTO(
            'app', null, 'prod', null, null, null, null, null, null, null,
            false, false, false, false,
        );
        self::assertNull($emptyOverlay->toArray()['customDomain']);

        $overlay = new \App\Domaining\DTO\DomainRuntimeOverlayDTO(
            'app', 'brand', 'prod', 'example.com', 'primary', 'published', 'active', 'published',
            'edge.smartresponsor.app', '/', true, true, true, true,
        );
        self::assertSame('example.com', $overlay->toArray()['customDomain']['domainName']);

        foreach ([
            new \App\Domaining\Event\DomainClaimed('claimed.example.com', 'owner-1'),
            new \App\Domaining\Event\DomainVerified('verified.example.com', 'owner-2'),
            new \App\Domaining\Event\DomainBindingActivated('active.example.com', 'owner-3'),
            new \App\Domaining\Event\DomainBindingSuspended('suspended.example.com', 'owner-4'),
        ] as $event) {
            self::assertStringEndsWith('.example.com', $event->domainName);
            self::assertStringStartsWith('owner-', $event->ownerId);
        }

        self::assertSame('Domain verification failed.', \App\Domaining\Exception\DomainVerificationException::create()->getMessage());
        self::assertSame('custom', \App\Domaining\Exception\DomainVerificationException::create('custom')->getMessage());

        self::assertTrue(\App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::canTransition('claimed', 'verification_pending'));
        self::assertTrue(\App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::canTransition('bound', 'bound'));
        self::assertFalse(\App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::canTransition('unknown', 'bound'));
        self::assertSame(['published', 'suspended', 'removed'], \App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::allowedTargets('bound'));
        self::assertSame([], \App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::allowedTargets('unknown'));
        \App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::assertCanTransition('verified', 'bound');
        self::addToAssertionCount(1);
        try {
            \App\Domaining\Policy\Lifecycle\DomainBindingLifecyclePolicy::assertCanTransition('removed', 'published');
            self::fail('Invalid binding transition must throw.');
        } catch (\DomainException $exception) {
            self::assertStringContainsString('removed', $exception->getMessage());
        }

        $claimPolicy = new \App\Domaining\Policy\Lifecycle\DomainClaimLifecyclePolicy();
        self::assertTrue($claimPolicy->canTransition(' Draft ', ' PENDING_VERIFICATION '));
        self::assertTrue($claimPolicy->canTransition('verified', 'VERIFIED'));
        self::assertFalse($claimPolicy->canTransition('archived', 'bound'));
        self::assertSame(['verified', 'failed', 'expired', 'cancelled'], $claimPolicy->allowedNextStatuses(' PENDING_VERIFICATION '));
        self::assertSame([], $claimPolicy->allowedNextStatuses('unknown'));
        $claimPolicy->assertCanTransition('failed', 'pending_verification');
        self::addToAssertionCount(1);
        try {
            $claimPolicy->assertCanTransition('cancelled', 'verified');
            self::fail('Invalid claim transition must throw.');
        } catch (\DomainException $exception) {
            self::assertStringContainsString('cancelled', $exception->getMessage());
        }
    }

    public function testAuditRuntimeOverlayAndTemplateFallbackServices(): void
    {
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn (object $record): bool => $record instanceof DomainAuditRecordEntity && 'audit.example.com' === $record->domainName()));
        (new \App\Domaining\Service\Audit\DomainAuditService(new \App\Domaining\Repository\DomainPersistenceRepository($entityManager)))->record('audit.example.com', 'domain_test', 'actor-1', ['source' => 'test']);

        $declarations = $this->createStub(\App\Domaining\RepositoryInterface\DomainDeclarationRepositoryInterface::class);
        $bindings = $this->createStub(\App\Domaining\RepositoryInterface\DomainBindingRepositoryInterface::class);
        $publications = $this->createStub(\App\Domaining\RepositoryInterface\DomainPublicationStateRepositoryInterface::class);
        $targets = $this->createStub(\App\Domaining\RepositoryInterface\DomainRoutingTargetRepositoryInterface::class);

        $declaration = new DomainDeclarationEntity('published-app', 'brand', 'prod', 'published.example.com', DomainApplicationRole::Primary);
        $declaration->markPublished();
        $binding = new DomainBindingEntity('published.example.com', 'owner-1', DomainSurfaceType::Application, 'published-app', $declaration);
        $binding->activate();
        $publication = new DomainPublicationStateEntity($binding);
        $publication->markPublished();
        $target = new DomainRoutingTargetEntity($binding, 'runtime.smartresponsor.app', '/app');

        $declarations->method('findPrimaryByApplication')->willReturnCallback(
            static fn (string $applicationKey): ?DomainDeclarationEntity => 'published-app' === $applicationKey ? $declaration : null,
        );
        $bindings->method('findOneForDeclaration')->willReturn($binding);
        $publications->method('findOneForBinding')->willReturn($publication);
        $targets->method('findOneForBinding')->willReturn($target);

        $overlayService = new \App\Domaining\Service\Runtime\DomainRuntimeOverlayService($declarations, $bindings, $publications, $targets);
        self::assertNull($overlayService->forApplication('missing-app', 'prod')->domainName);
        $overlay = $overlayService->forApplication(' published-app ', ' prod ');
        self::assertSame('runtime.smartresponsor.app', $overlay->targetHost);
        self::assertTrue($overlay->customDomainPublished);
        try {
            $overlayService->forApplication('', 'prod');
            self::fail('Empty application key must throw.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('must not be empty', $exception->getMessage());
        }

        $payload = new \App\Domaining\DTO\DomainInterfacingPayloadDTO(
            'domaining.interfacing.v1', 'domain', 'render.example.com', 'owner-1', 'application', 'main',
            ['primary' => 'main'], ['status' => 'active'], ['status' => 'published'], ['slot' => 'domain'],
        );
        $fallbackTwig = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));
        $fallback = (new \App\Domaining\Service\Interfacing\DomainTemplateRenderService($fallbackTwig, 'domain', ['missing.html.twig']))->render($payload);
        self::assertFalse($fallback->rendered);

        $twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader([
            'domain.html.twig' => '<h1>{{ title }}</h1>',
        ]));
        $rendered = (new \App\Domaining\Service\Interfacing\DomainTemplateRenderService($twig, 'domain', ['', 'missing.html.twig', 'domain.html.twig']))->render($payload);
        self::assertTrue($rendered->rendered);
        self::assertSame('domain.html.twig', $rendered->template);
        self::assertStringContainsString('render.example.com', (string) $rendered->html);
    }

    public function testPersistenceRepositoryPersistsAndFlushesAsOneUnitOfWork(): void
    {
        $entity = new \stdClass();
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($entity);
        $entityManager->expects(self::once())->method('flush');

        $repository = new \App\Domaining\Repository\DomainPersistenceRepository($entityManager);
        $repository->persistAndFlush($entity);
    }

    public function testSymfonyFormsAndEasyAdminDeclarationSurface(): void
    {
        $factory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
            ->addType(new \App\Domaining\Form\DomainClaimType())
            ->addType(new \App\Domaining\Form\DomainDeclarationType())
            ->getFormFactory();

        $claimForm = $factory->create(\App\Domaining\Form\DomainClaimType::class);
        self::assertSame(['domainName', 'ownerId', 'surfaceType', 'surfaceKey'], array_keys(iterator_to_array($claimForm)));
        self::assertNotEmpty($claimForm->createView()->children);

        $declarationForm = $factory->create(\App\Domaining\Form\DomainDeclarationType::class);
        self::assertSame(['applicationKey', 'brandKey', 'environment', 'domainName', 'role'], array_keys(iterator_to_array($declarationForm)));
        self::assertNotEmpty($declarationForm->createView()->children);

        $controller = new \App\Domaining\Controller\Admin\DomainDeclarationCrudController();
        self::assertSame(DomainDeclarationEntity::class, \App\Domaining\Controller\Admin\DomainDeclarationCrudController::getEntityFqcn());
        self::assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class, $controller->configureCrud(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::new()));
        self::assertInstanceOf(DomainDeclarationEntity::class, $controller->createEntity(DomainDeclarationEntity::class));
        self::assertCount(10, iterator_to_array($controller->configureFields(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX)));
    }

    public function testReportCommandsCoverFailureExitContracts(): void
    {
        $now = new DateTimeImmutable();

        $governance = $this->createStub(\App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface::class);
        $governance->method('buildReport')->willReturn(new \App\Domaining\DTO\DomainContractGovernanceReportDTO('v1', $now, false, [], [], [], []));
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainContractGovernanceCommand($governance)))->execute([]));

        $diagnostic = $this->createStub(\App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface::class);
        $diagnostic->method('buildReport')->willReturn(new \App\Domaining\DTO\DomainDiagnosticReportDTO($now, false, [], []));
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainDiagnosticReportCommand($diagnostic)))->execute([]));

        $gate = $this->createStub(\App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface::class);
        $gate->method('evaluate')->willReturn(new \App\Domaining\DTO\DomainReleaseGateReportDTO(false, ['blocked'], [], []));
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleaseGateCommand($gate)))->execute([]));

        $manifest = $this->createStub(\App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface::class);
        $manifest->method('buildManifest')->willReturn(new \App\Domaining\DTO\DomainReleaseManifestDTO(
            'v1', $now, 'Domaining', 'domaining/domain', 'App\\Domaining\\', 'domain', 'domain_', false, [], [], [], [], [],
        ));
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleaseManifestCommand($manifest)))->execute([]));

        $package = $this->createStub(\App\Domaining\ServiceInterface\Package\DomainReleasePackageServiceInterface::class);
        $package->method('buildPackage')->willReturnOnConsecutiveCalls(
            new \App\Domaining\DTO\DomainReleasePackageReportDTO(
                'v1', $now, false, 'Domaining', 'domaining/domain', [], [], [], [], [],
            ),
            new \App\Domaining\DTO\DomainReleasePackageReportDTO(
                'v1', $now, true, 'Domaining', 'domaining/domain', [], [], [], [], [],
            ),
        );
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleasePackageCommand($package)))->execute([]));
        self::assertSame(0, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleasePackageCommand($package)))->execute([]));

        $review = $this->createStub(\App\Domaining\ServiceInterface\Review\DomainReleaseReviewServiceInterface::class);
        $review->method('buildReport')->willReturnOnConsecutiveCalls(
            new \App\Domaining\DTO\DomainReleaseReviewReportDTO('v1', $now, false, [], []),
            new \App\Domaining\DTO\DomainReleaseReviewReportDTO('v1', $now, true, [], []),
        );
        self::assertSame(1, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleaseReviewCommand($review)))->execute([]));
        self::assertSame(0, (new \Symfony\Component\Console\Tester\CommandTester(new \App\Domaining\Command\DomainReleaseReviewCommand($review)))->execute([]));
    }

    private function binding(): DomainBindingEntity
    {
        return new DomainBindingEntity('example.com', 'vendor-1', DomainSurfaceType::Application, 'main');
    }
}
