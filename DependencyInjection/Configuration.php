<?php

declare(strict_types=1);

namespace ViewComponentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('view_component');

        $rootNode
            ->children()
                ->arrayNode('component_dirs')
                    ->isRequired()
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('template_dirs')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
