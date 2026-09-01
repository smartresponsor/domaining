<?php

declare(strict_types=1);

namespace App\Domaining\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class DomainingExtension extends Extension
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('domaining.standalone_enabled', (bool) $config['standalone_enabled']);
        $container->setParameter('domaining.template_surface', (string) $config['template_surface']);
        $container->setParameter('domaining.raw_json_fallback', (bool) $config['raw_json_fallback']);
        $container->setParameter('domaining.interfacing_template_candidates', $config['interfacing_template_candidates']);
        $container->setParameter('domaining.provider_recommendation', $config['provider_recommendation']);

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2).'/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'domaining';
    }
}
