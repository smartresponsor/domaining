<?php

declare(strict_types=1);

namespace App\Domaining\Test\Integration;

use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\Entity\DomainClaimEntity;
use App\Domaining\Entity\DomainPublicationStateEntity;
use App\Domaining\Entity\DomainRoutingTargetEntity;
use App\Domaining\Entity\DomainVerificationChallengeEntity;
use App\Domaining\Enum\DomainRecordType;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Kernel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DomainConsoleReportFlowTest extends TestCase
{
    private Kernel $kernel;
    private EntityManagerInterface $entityManager;
    private Application $application;

    protected function setUp(): void
    {
        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();

        $registry = $this->kernel->getContainer()->get('doctrine');
        self::assertInstanceOf(Registry::class, $registry);

        $manager = $registry->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        $this->entityManager = $manager;

        $metadata = array_values(array_filter(
            $this->entityManager->getMetadataFactory()->getAllMetadata(),
            static fn ($class): bool => str_starts_with($class->getName(), 'App\\Domaining\\Entity\\'),
        ));
        self::assertNotEmpty($metadata);

        (new SchemaTool($this->entityManager))->createSchema($metadata);

        $this->seedLifecycleState();

        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->kernel->shutdown();
    }

    public function testProviderNeutralOperationalCommandsRunAgainstRealDoctrineState(): void
    {
        $commands = [
            ['domaining:publication:intent-export', [], 'domainPublication'],
            ['domaining:diagnostic:report', [], 'domainDiagnosticReport'],
            ['domaining:state:export', [], 'domainStateExport'],
            ['domaining:runtime:handoff-export', [], 'domainRuntimeHandoff'],
            ['domaining:contract:governance', [], 'domainContractGovernance'],
            ['domaining:release:manifest', [], 'domainReleaseManifest'],
            ['domaining:release:review', [], 'domainReleaseReview'],
            ['domaining:release:package', [], 'domainReleasePackage'],
        ];

        foreach ($commands as [$name, $arguments, $rootKey]) {
            [$status, $text] = $this->runCommand($name, $arguments);

            self::assertContains($status, [Command::SUCCESS, Command::FAILURE], $name);
            self::assertStringContainsString($rootKey, $text, $name);

            $payload = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($payload);
            self::assertArrayHasKey($rootKey, $payload);
        }
    }

    public function testReleaseGateAndSurfacePolicyCommandsExposeActionableState(): void
    {
        [$gateStatus, $gateText] = $this->runCommand('domaining:release:gate');
        self::assertSame(Command::SUCCESS, $gateStatus);

        $gate = json_decode($gateText, true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($gate['passed']);
        self::assertGreaterThanOrEqual(2, $gate['checks']['active_bindings']);
        self::assertGreaterThanOrEqual(1, $gate['checks']['suspended_bindings']);
        self::assertNotEmpty($gate['warnings']);

        [$policyStatus, $policyText] = $this->runCommand('domaining:policy:surface-report', [
            'ownerId' => 'vendor-1',
            '--surface-type' => DomainSurfaceType::Application->value,
            '--surface-key' => 'published',
        ]);
        self::assertContains($policyStatus, [Command::SUCCESS, Command::FAILURE]);

        $policy = json_decode($policyText, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('vendor-1', $policy['ownerId']);
        self::assertSame(DomainSurfaceType::Application->value, $policy['surfaceType']);
    }

    public function testReleaseGateBlocksActiveBindingWithoutVerificationEvidence(): void
    {
        $binding = new DomainBindingEntity(
            'unverified-active.example.com',
            'vendor-unverified',
            DomainSurfaceType::Application,
            'unverified-active',
        );
        $binding->activate();
        $this->entityManager->persist($binding);
        $this->entityManager->flush();

        [$status, $text] = $this->runCommand('domaining:release:gate');

        self::assertSame(Command::FAILURE, $status);
        $gate = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($gate['passed']);
        self::assertGreaterThanOrEqual(1, $gate['checks']['active_bindings_without_verification']);
        self::assertContains(
            'Active domain bindings exist without recorded ownership verification timestamps.',
            $gate['errors'],
        );
    }

    public function testConsumerEnsureCoversDeclarationOnlyAndClaimChallengeFlows(): void
    {
        [$declarationStatus, $declarationText] = $this->runCommand('domaining:consumer:ensure', [
            '--domain' => 'declared.example.com',
            '--application' => 'portal',
            '--brand' => 'brand',
            '--environment' => 'test',
            '--declaration-only' => true,
        ]);

        self::assertSame(Command::SUCCESS, $declarationStatus);
        self::assertStringContainsString('Declaration: declared.example.com', $declarationText);

        [$claimStatus, $claimText] = $this->runCommand('domaining:consumer:ensure', [
            '--domain' => 'claimed.example.com',
            '--application' => 'portal-claim',
            '--brand' => 'brand',
            '--environment' => 'test',
            '--owner' => 'vendor-2',
            '--surface' => DomainSurfaceType::Application->value,
            '--surface-key' => 'portal',
        ]);

        self::assertSame(Command::SUCCESS, $claimStatus);
        self::assertStringContainsString('Claim: claimed.example.com [challenge_issued]', $claimText);
        self::assertStringContainsString('TXT name: _smartresponsor-domain.claimed.example.com', $claimText);
        self::assertStringContainsString('sr-domain-verification=', $claimText);
    }

    public function testConsumerEnsureRejectsIncompleteInput(): void
    {
        [$status, $text] = $this->runCommand('domaining:consumer:ensure', [
            '--domain' => '',
            '--application' => 'portal',
            '--brand' => 'brand',
        ]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('must be valid', $text);
    }

    public function testHttpApiSurfacesExerciseProviderNeutralApplicationFlows(): void
    {
        $published = $this->entityManager->getRepository(DomainBindingEntity::class)->findOneBy(['domainName' => 'published.example.com']);
        self::assertInstanceOf(DomainBindingEntity::class, $published);

        $challenge = $this->entityManager->getRepository(DomainVerificationChallengeEntity::class)->findOneBy(['recordName' => '_smartresponsor-domain.expired.example.com']);
        self::assertInstanceOf(DomainVerificationChallengeEntity::class, $challenge);

        foreach ([
            '/domain/configuration/tool/metadata',
            '/domain/contract/governance',
            '/domain/diagnostic/report',
            '/domain/export/state',
            '/domain/release/gate',
            '/domain/release/manifest',
            '/domain/release/review',
            '/domain/release/package',
            '/domain/runtime/handoff',
            '/domain/observability/metric/snapshot',
            '/domain/policy/surface/vendor-1?surfaceType=application&surfaceKey=published',
            '/domain/publication/'.(string) $published->id().'/snapshot',
            '/domain/surface/'.(string) $published->id(),
            '/domain/verification/'.(string) $challenge->id().'/instruction?provider=cloudflare',
        ] as $path) {
            $response = $this->request('GET', $path);
            self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $path);
            self::assertStringStartsWith('application/json', (string) $response->headers->get('content-type'), $path);
        }

        $readiness = $this->request('GET', '/domain/observability/readiness');
        self::assertContains($readiness->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CONFLICT]);

        $expired = $this->request('POST', '/domain/verification/'.(string) $challenge->id().'/check?force=1');
        self::assertSame(Response::HTTP_OK, $expired->getStatusCode());
        self::assertStringContainsString('expired', strtolower($expired->getContent() ?: ''));

        $claimResponse = $this->request('POST', '/domain/claim', [
            'domainName' => 'http.example.com',
            'ownerId' => 'vendor-http',
            'surfaceType' => DomainSurfaceType::Application->value,
            'surfaceKey' => 'http',
        ]);
        self::assertSame(Response::HTTP_CREATED, $claimResponse->getStatusCode());
        self::assertStringContainsString('sr-domain-verification=', $claimResponse->getContent() ?: '');

        $verifiedClaim = new DomainClaimEntity('binding-http.example.com', 'vendor-http', DomainSurfaceType::Application, 'binding-http');
        $verifiedClaim->markVerified();
        $this->entityManager->persist($verifiedClaim);
        $this->entityManager->flush();

        $bindingResponse = $this->request('POST', '/domain/binding/from/claim/'.(string) $verifiedClaim->id());
        self::assertSame(Response::HTTP_CREATED, $bindingResponse->getStatusCode());
        $bindingPayload = json_decode($bindingResponse->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        $bindingId = (string) $bindingPayload['bindingId'];

        $routingResponse = $this->request('POST', '/domain/binding/'.$bindingId.'/routing/intent', [
            'targetHost' => 'binding-http.smartresponsor.app',
            'targetPath' => '/tenant',
        ]);
        self::assertSame(Response::HTTP_OK, $routingResponse->getStatusCode());
        self::assertStringContainsString('binding-http.smartresponsor.app', $routingResponse->getContent() ?: '');

        $binding = $this->entityManager->getRepository(DomainBindingEntity::class)->find($bindingId);
        self::assertInstanceOf(DomainBindingEntity::class, $binding);
        $binding->activate();
        $this->entityManager->flush();

        $publishedResponse = $this->request('POST', '/domain/publication/'.$bindingId.'/published');
        self::assertSame(Response::HTTP_OK, $publishedResponse->getStatusCode());

        $binding = $this->entityManager->getRepository(DomainBindingEntity::class)->find($bindingId);
        self::assertInstanceOf(DomainBindingEntity::class, $binding);
        $binding->suspend();
        $this->entityManager->flush();
        $withdrawnResponse = $this->request('POST', '/domain/publication/'.$bindingId.'/withdrawn');
        self::assertSame(Response::HTTP_OK, $withdrawnResponse->getStatusCode());

        $auditResponse = $this->request('GET', '/domain/audit/http.example.com?limit=250');
        self::assertSame(Response::HTTP_OK, $auditResponse->getStatusCode());
        self::assertStringContainsString('domain_claim_created', $auditResponse->getContent() ?: '');
    }

    public function testCustomRepositoriesAndKernelContractsCoverPositiveAndEmptyQueries(): void
    {
        self::assertNotEmpty(iterator_to_array($this->kernel->registerBundles()));
        self::assertSame(dirname(__DIR__, 2), $this->kernel->getProjectDir());

        $passed = new \App\Domaining\DTO\DomainVerificationResultDTO(\App\Domaining\Enum\DomainVerificationStatus::Passed, 'passed');
        $failed = new \App\Domaining\DTO\DomainVerificationResultDTO(\App\Domaining\Enum\DomainVerificationStatus::Failed, 'failed');
        self::assertTrue($passed->passed());
        self::assertFalse($failed->passed());

        $declaration = new \App\Domaining\Entity\DomainDeclarationEntity('repository-app', 'brand', 'test', 'repository.example.com', \App\Domaining\Enum\DomainApplicationRole::Primary);
        $unboundDeclaration = new \App\Domaining\Entity\DomainDeclarationEntity('unbound-app', 'brand', 'test', 'unbound.example.com', \App\Domaining\Enum\DomainApplicationRole::Primary);
        $binding = new DomainBindingEntity('repository.example.com', 'repository-owner', DomainSurfaceType::Application, 'repository', $declaration);
        $binding->activate();
        $publication = new DomainPublicationStateEntity($binding);
        $publication->markReady();
        $target = new DomainRoutingTargetEntity($binding, 'repository.smartresponsor.app', '/repository');
        $pendingClaim = new DomainClaimEntity('pending-ready.example.com', 'repository-owner', DomainSurfaceType::Application, 'pending-ready');
        $pendingChallenge = new DomainVerificationChallengeEntity($pendingClaim, DomainRecordType::Txt, '_smartresponsor-domain.pending-ready.example.com', 'sr-domain-verification=pending-ready', new DateTimeImmutable('+1 hour'));
        $audit = new \App\Domaining\Entity\DomainAuditRecordEntity('repository.example.com', 'repository_test', 'tester');

        foreach ([$declaration, $unboundDeclaration, $binding, $publication, $target, $pendingClaim, $pendingChallenge, $audit] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        $declarations = $this->entityManager->getRepository(\App\Domaining\Entity\DomainDeclarationEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainDeclarationRepository::class, $declarations);
        self::assertSame($declaration->id(), $declarations->findOneById((string) $declaration->id())?->id());
        self::assertSame($declaration->id(), $declarations->findOneByDomainAndEnvironment(' Repository.Example.COM. ', ' test ')?->id());
        self::assertNull($declarations->findOneByDomainAndEnvironment('missing.example.com', 'test'));
        self::assertSame($declaration->id(), $declarations->findPrimaryByApplication(' repository-app ', ' test ')?->id());
        self::assertNull($declarations->findPrimaryByApplication('missing-app', 'test'));
        self::assertCount(1, $declarations->findByApplication(' repository-app ', ' test '));
        self::assertSame([], $declarations->findByApplication('missing-app', 'test'));

        $bindings = $this->entityManager->getRepository(DomainBindingEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainBindingRepository::class, $bindings);
        self::assertSame($binding->id(), $bindings->findActiveByDomainName('repository.example.com')?->id());
        self::assertNull($bindings->findActiveByDomainName('missing.example.com'));
        self::assertSame($binding->id(), $bindings->findLiveByDomainName('repository.example.com')?->id());
        self::assertNull($bindings->findLiveByDomainName('removed.example.com'));
        self::assertSame($binding->id(), $bindings->findOneForDeclaration($declaration)?->id());
        self::assertNull($bindings->findOneForDeclaration($unboundDeclaration));

        $publications = $this->entityManager->getRepository(DomainPublicationStateEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainPublicationStateRepository::class, $publications);
        self::assertSame($publication->id(), $publications->findOneForBinding($binding)?->id());
        $unbound = new DomainBindingEntity('repository-unbound.example.com', 'repository-owner', DomainSurfaceType::Application, 'unbound');
        self::assertNull($publications->findOneForBinding($unbound));

        $targets = $this->entityManager->getRepository(DomainRoutingTargetEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainRoutingTargetRepository::class, $targets);
        self::assertSame($target->id(), $targets->findOneForBinding($binding)?->id());
        self::assertNull($targets->findOneForBinding($unbound));

        $challenges = $this->entityManager->getRepository(DomainVerificationChallengeEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainVerificationChallengeRepository::class, $challenges);
        $ready = $challenges->findPendingReadyForCheck(new DateTimeImmutable(), 10);
        self::assertCount(1, array_filter($ready, static fn (DomainVerificationChallengeEntity $item): bool => (string) $item->id() === (string) $pendingChallenge->id()));

        $audits = $this->entityManager->getRepository(\App\Domaining\Entity\DomainAuditRecordEntity::class);
        self::assertInstanceOf(\App\Domaining\Repository\DomainAuditRecordRepository::class, $audits);
        self::assertCount(1, $audits->recentForDomain('repository.example.com', 0));
        self::assertCount(1, $audits->recentForDomain('repository.example.com', 500));
    }

    /** @param array<string, mixed>|null $payload */
    private function request(string $method, string $path, ?array $payload = null): Response
    {
        $content = null === $payload ? null : json_encode($payload, JSON_THROW_ON_ERROR);
        $server = null === $payload ? [] : ['CONTENT_TYPE' => 'application/json'];

        return $this->kernel->handle(Request::create($path, $method, [], [], [], $server, $content));
    }

    private function seedLifecycleState(): void
    {
        $published = $this->binding('published.example.com', 'published');
        $published->markVerifiedNow();
        $published->activate();
        $publishedState = new DomainPublicationStateEntity($published);
        $publishedState->markReady();
        $publishedState->markPublished();
        $publishedRoute = new DomainRoutingTargetEntity($published, 'published.smartresponsor.app', '/');

        $ready = $this->binding('ready.example.com', 'ready');
        $ready->markVerifiedNow();
        $ready->activate();
        $readyState = new DomainPublicationStateEntity($ready);
        $readyState->markReady();
        $readyRoute = new DomainRoutingTargetEntity($ready, 'ready.smartresponsor.app', '/app');

        $suspended = $this->binding('suspended.example.com', 'suspended');
        $suspended->markVerifiedNow();
        $suspended->suspend();
        $suspendedState = new DomainPublicationStateEntity($suspended);
        $suspendedState->markReady();
        $suspendedState->markPublished();
        $suspendedRoute = new DomainRoutingTargetEntity($suspended, 'suspended.smartresponsor.app');

        $verified = $this->binding('verified.example.com', 'verified');
        $verified->markVerifiedNow();

        $blocked = $this->binding('blocked.example.com', 'blocked');

        $removed = $this->binding('removed.example.com', 'removed');
        $removed->remove();

        $claim = new DomainClaimEntity(
            'expired.example.com',
            'vendor-1',
            DomainSurfaceType::Application,
            'expired',
        );
        $challenge = new DomainVerificationChallengeEntity(
            $claim,
            DomainRecordType::Txt,
            '_smartresponsor-domain.expired.example.com',
            'sr-domain-verification=expired',
            new DateTimeImmutable('-1 hour'),
        );
        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $challenge->markChecked('not propagated', 60);
        }

        foreach ([
            $published,
            $publishedState,
            $publishedRoute,
            $ready,
            $readyState,
            $readyRoute,
            $suspended,
            $suspendedState,
            $suspendedRoute,
            $verified,
            $blocked,
            $removed,
            $claim,
            $challenge,
        ] as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    private function binding(string $domainName, string $surfaceKey): DomainBindingEntity
    {
        return new DomainBindingEntity(
            $domainName,
            'vendor-1',
            DomainSurfaceType::Application,
            $surfaceKey,
        );
    }

    /**
     * @param array<string, bool|string> $arguments
     * @return array{int, string}
     */
    private function runCommand(string $name, array $arguments = []): array
    {
        $input = new ArrayInput(['command' => $name, ...$arguments]);
        $input->setInteractive(false);
        $output = new BufferedOutput();

        $status = $this->application->run($input, $output);

        return [$status, trim($output->fetch())];
    }
}
