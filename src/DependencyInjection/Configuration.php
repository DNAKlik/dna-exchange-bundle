<?php

namespace DnaKlik\DnaExchangeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): ?TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dnaklik_dna_exchange');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('crossOver')->defaultValue(8)->info('Number off stamps to exchange')->end()
                ->integerNode('maxStamps')->defaultValue(64)->info('Max stamps per item')->end()
                ->scalarNode('stamp_provider')->defaultNull()->end()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stylesheets')
                            ->defaultValue([
                                'bundles/dnaklikdnaexchange/build/app.css'
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_stylesheets')
                            ->info('stylesheets to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_stylesheets')
                            ->info('stylesheets to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->defaultValue([
                                'bundles/dnaklikdnaexchange/build/app.js'
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_javascripts')
                            ->info('javascripts to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_javascripts')
                            ->info('javascripts to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()

                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
