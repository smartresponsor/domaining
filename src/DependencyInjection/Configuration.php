<?php

declare(strict_types=1);

namespace App\Domaining\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('domaining');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('standalone_enabled')->defaultFalse()->end()
                ->scalarNode('template_surface')->defaultValue('domain')->end()
                ->booleanNode('raw_json_fallback')->defaultTrue()->end()
                ->arrayNode('interfacing_template_candidates')
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        '@@Interfacing/domain/base.twig',
                        'interfacing/domain/base.twig',
                        'domain/base.twig',
                        '@@Interfacing/domain/surface/binding.twig',
                        'interfacing/domain/surface/binding.twig',
                        'domain/surface/binding.twig',
                    ])
                ->end()
                ->arrayNode('provider_recommendation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('primary')->defaultValue('Cloudflare')->end()
                        ->scalarNode('note')->defaultValue('Smart Responsor does not register, sell, transfer, or host domains as a registrar.')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
