<?php

declare(strict_types=1);

namespace App\Domaining\Service\Configuration;

use App\Domaining\Dto\DomainConfigurationToolDescriptor;
use App\Domaining\Dto\DomainConfigurationVariable;
use App\Domaining\Enum\DomainConfigurationTarget;
use App\Domaining\Enum\DomainPublicationMode;
use App\Domaining\ServiceInterface\Configuration\DomainConfigurationToolMetadataServiceInterface;

final readonly class DomainConfigurationToolMetadataService implements DomainConfigurationToolMetadataServiceInterface
{
    public function descriptor(): DomainConfigurationToolDescriptor
    {
        return new DomainConfigurationToolDescriptor(
            key: 'domaining.domain.connection',
            label: 'Custom domain connection',
            section: 'domain',
            description: 'Provider-neutral configuration metadata for connecting externally registered domains to Smart Responsor surfaces.',
            variables: [
                new DomainConfigurationVariable(
                    key: 'DOMAIN_PUBLICATION_MODE',
                    label: 'Publication mode',
                    description: 'Controls whether Domaining only prepares routing intent or allows a runtime provider to mark a binding as published.',
                    target: DomainConfigurationTarget::Environment,
                    type: 'enum',
                    required: true,
                    allowedValues: array_map(static fn (DomainPublicationMode $mode): string => $mode->value, DomainPublicationMode::cases()),
                ),
                new DomainConfigurationVariable(
                    key: 'DOMAIN_ROUTING_TARGET_HOST',
                    label: 'Default routing target host',
                    description: 'Fallback host suggested in routing intent when a request does not provide a target host.',
                    target: DomainConfigurationTarget::Environment,
                    type: 'hostname',
                    required: true,
                ),
                new DomainConfigurationVariable(
                    key: 'DOMAIN_VERIFICATION_TOKEN_PREFIX',
                    label: 'Verification token prefix',
                    description: 'Plain prefix used inside DNS TXT challenge values before the random verification token.',
                    target: DomainConfigurationTarget::Environment,
                    type: 'string',
                    required: true,
                ),
                new DomainConfigurationVariable(
                    key: 'DOMAIN_VERIFICATION_RETRY_SECONDS',
                    label: 'Verification retry window',
                    description: 'Minimum age in seconds before a pending DNS challenge should be rechecked by the scheduled command.',
                    target: DomainConfigurationTarget::Environment,
                    type: 'integer',
                    required: true,
                ),
                new DomainConfigurationVariable(
                    key: 'DOMAIN_DNS_CHECK_TIMEOUT_SECONDS',
                    label: 'DNS check timeout',
                    description: 'Maximum DNS resolver wait time for one verification lookup.',
                    target: DomainConfigurationTarget::Environment,
                    type: 'integer',
                    required: true,
                ),
            ],
            capabilities: [
                'domain.claim',
                'domain.dns_instruction',
                'domain.verification_recheck',
                'domain.binding_activation',
                'domain.routing_intent',
            ],
            metadata: [
                'namespace' => 'App\Domaining',
                'business_prefix' => 'Domain',
                'storage_prefix' => 'domain_',
                'provider_policy' => 'provider_neutral_non_registrar',
            ],
        );
    }
}
