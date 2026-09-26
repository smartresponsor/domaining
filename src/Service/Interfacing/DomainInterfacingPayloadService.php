<?php

declare(strict_types=1);

namespace App\Domaining\Service\Interfacing;

use App\Domaining\DTO\DomainInterfacingPayloadDTO;
use App\Domaining\Entity\DomainBindingEntity;
use App\Domaining\ServiceInterface\Interfacing\DomainInterfacingPayloadServiceInterface;
use App\Domaining\ServiceInterface\Publication\DomainPublicationReadServiceInterface;

final readonly class DomainInterfacingPayloadService implements DomainInterfacingPayloadServiceInterface
{
    private const SCHEMA_VERSION = 'domaining.interfacing-domain-surface.v1';

    /**
     * @var list<string>
     */
    private const SLOT_CONTRACT = [
        'shell.profile.cover',
        'shell.profile.identity',
        'shell.body.top',
        'shell.header.bottom',
        'shell.left.top',
        'shell.left.middle',
        'shell.left.bottom',
        'shell.context.top',
        'shell.context.middle',
        'shell.context.bottom',
        'shell.main.top',
        'shell.main.toolbar',
        'shell.main.content',
        'shell.main.bottom',
        'shell.right.top',
        'shell.right.tool',
        'shell.right.filter',
        'shell.right.middle',
        'shell.right.bottom',
        'shell.footer.top',
        'shell.footer.left',
        'shell.footer.context',
        'shell.footer.main',
        'shell.footer.right',
    ];

    public function __construct(
        private DomainPublicationReadServiceInterface $publicationReadService,
    ) {
    }

    public function payloadForBinding(DomainBindingEntity $binding): DomainInterfacingPayloadDTO
    {
        $publication = $this->publicationReadService->snapshot($binding)->toArray();
        $bindingData = [
            'id' => $binding->id()->toRfc4122(),
            'domainName' => $binding->domainName(),
            'ownerId' => $binding->ownerId(),
            'surfaceType' => $binding->surfaceType()->value,
            'surfaceKey' => $binding->surfaceKey(),
            'status' => $binding->status()->value,
            'lastVerifiedAt' => $binding->lastVerifiedAt()?->format(DATE_ATOM),
        ];

        return new DomainInterfacingPayloadDTO(
            self::SCHEMA_VERSION,
            'domain',
            $binding->domainName(),
            $binding->ownerId(),
            $binding->surfaceType()->value,
            $binding->surfaceKey(),
            $this->locations($bindingData, $publication),
            $bindingData,
            $publication,
            [
                'mount' => '@Interfacing/domain/binding.html.twig',
                'surface' => 'domain',
                'slots' => self::SLOT_CONTRACT,
                'fallback' => 'Return structured JSON when the Interfacing domain template is unavailable.',
            ],
        );
    }

    /**
     * @param array<string, mixed> $binding
     * @param array<string, mixed> $publication
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function locations(array $binding, array $publication): array
    {
        $status = (string) $binding['status'];
        $domainName = (string) $binding['domainName'];
        $surfaceLabel = sprintf('%s / %s', (string) $binding['surfaceType'], (string) $binding['surfaceKey']);
        $publicationStatus = (string) ($publication['status'] ?? 'unknown');
        $targetHost = $publication['targetHost'] ?? null;
        $targetPath = $publication['targetPath'] ?? null;

        return [
            'shell.profile.cover' => [
                $this->image('Domain', '/domain.svg', 'Domain surface'),
            ],
            'shell.profile.identity' => [
                $this->title('Custom domain', $domainName),
                $this->text('Domaining', 'Custom-domain lifecycle'),
                $this->status('Binding status', $status),
            ],
            'shell.body.top' => [
                $this->notice('Domain connection surface', 'This surface connects an externally registered domain to Smart Responsor. Smart Responsor does not act as registrar, DNS host, or domain provider.'),
            ],
            'shell.header.bottom' => [
                $this->metric('Owner', (string) $binding['ownerId']),
                $this->metric('Surface', $surfaceLabel),
                $this->status('Publication', $publicationStatus),
                $this->text('Verification', null === $binding['lastVerifiedAt'] ? 'Verification timestamp is not available yet.' : (string) $binding['lastVerifiedAt']),
            ],
            'shell.left.top' => [
                $this->nav('Domain overview', '#domain-overview'),
                $this->nav('DNS instruction', '#domain-dns'),
                $this->nav('Publication', '#domain-publication'),
            ],
            'shell.left.middle' => [
                $this->action('Check DNS now', 'check_dns'),
                $this->action('Prepare publication', 'prepare_publication'),
                $this->action('Suspend binding', 'suspend_binding'),
            ],
            'shell.left.bottom' => [
                $this->text('Provider note', 'Cloudflare may be recommended, but provider ownership remains external to Smart Responsor.'),
            ],
            'shell.context.top' => [
                $this->metric('Domain', $domainName),
                $this->metric('Binding', $status),
                $this->metric('Runtime', $publicationStatus),
            ],
            'shell.context.middle' => [
                $this->text('Target host', is_string($targetHost) && '' !== $targetHost ? $targetHost : 'Not assigned'),
                $this->text('Target path', is_string($targetPath) && '' !== $targetPath ? $targetPath : '/'),
            ],
            'shell.context.bottom' => [
                $this->text('Template surface', 'domain'),
                $this->text('Mount', '@Interfacing/domain/binding.html.twig'),
            ],
            'shell.main.top' => [
                $this->component('DomainBindingSummary', 'ant-design', $binding),
                $this->component('DomainStatusStrip', 'prime-react', ['binding' => $binding, 'publication' => $publication]),
            ],
            'shell.main.toolbar' => [
                $this->action('Refresh status', 'recheck_verification'),
                $this->action('Prepare publication', 'prepare_publication'),
            ],
            'shell.main.content' => [
                $this->component('DomainWorkbench', 'pro-component', ['binding' => $binding, 'publication' => $publication]),
                $this->component('DomainVerificationSteps', 'ant-design', ['binding' => $binding]),
            ],
            'shell.main.bottom' => [
                $this->notice('Runtime handoff', 'Domaining exports provider-neutral routing intent. Runtime providers apply infrastructure changes outside this component.'),
            ],
            'shell.right.top' => [
                $this->component('DomainDiagnosticCard', 'prime-react', ['binding' => $binding, 'publication' => $publication]),
            ],
            'shell.right.tool' => [
                $this->action('Check DNS now', 'check_dns'),
            ],
            'shell.right.filter' => [],
            'shell.right.middle' => [
                $this->text('Next safe action', $this->nextAction($status, $publicationStatus)),
            ],
            'shell.right.bottom' => [
                $this->text('Audit', 'Review claim, verification, publication and suspension events before release.'),
            ],
            'shell.footer.top' => [
                $this->text('Boundary', 'Claim, verify, bind, publish readiness, export runtime intent.'),
            ],
            'shell.footer.left' => [
                $this->text('Component', 'Domaining'),
            ],
            'shell.footer.context' => [
                $this->text('Business prefix', 'Domain*'),
            ],
            'shell.footer.main' => [
                $this->text('Provider-neutral posture', 'No registrar/provider ownership, no Cloudflare hard dependency, no infrastructure mutation.'),
            ],
            'shell.footer.right' => [
                $this->text('Schema', self::SCHEMA_VERSION),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function title(string $label, string $description): array
    {
        return ['type' => 'title', 'label' => $label, 'description' => $description];
    }

    /**
     * @return array<string, mixed>
     */
    private function text(string $label, string $description): array
    {
        return ['type' => 'text', 'label' => $label, 'description' => $description];
    }

    /**
     * @return array<string, mixed>
     */
    private function notice(string $label, string $description): array
    {
        return ['type' => 'notice', 'label' => $label, 'description' => $description];
    }

    /**
     * @return array<string, mixed>
     */
    private function metric(string $label, string $value): array
    {
        return ['type' => 'metric', 'label' => $label, 'value' => $value];
    }

    /**
     * @return array<string, mixed>
     */
    private function status(string $label, string $value): array
    {
        return ['type' => 'status', 'label' => $label, 'value' => $value];
    }

    /**
     * @return array<string, mixed>
     */
    private function nav(string $label, string $href): array
    {
        return ['type' => 'link', 'label' => $label, 'href' => $href];
    }

    /**
     * @return array<string, mixed>
     */
    private function action(string $label, string $action): array
    {
        return ['type' => 'action', 'label' => $label, 'action' => $action];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function component(string $component, string $provider, array $payload): array
    {
        return ['type' => 'component', 'provider' => $provider, 'component' => $component, 'payload' => $payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function image(string $label, string $src, string $alt): array
    {
        return ['type' => 'image', 'label' => $label, 'src' => $src, 'alt' => $alt];
    }

    private function nextAction(string $bindingStatus, string $publicationStatus): string
    {
        if ('verified' === $bindingStatus && 'not_ready' === $publicationStatus) {
            return 'Prepare publication intent.';
        }

        if ('active' === $bindingStatus && 'ready' === $publicationStatus) {
            return 'Runtime provider should publish route.';
        }

        if ('suspended' === $bindingStatus && 'published' === $publicationStatus) {
            return 'Runtime provider should withdraw route.';
        }

        return 'Review diagnostics and release gate.';
    }
}
