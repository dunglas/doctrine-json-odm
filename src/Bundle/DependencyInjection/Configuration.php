<?php

namespace Dunglas\DoctrineJsonOdm\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dunglas_doctrine_json_odm');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('types')
                    ->defaultValue([])
                    ->useAttributeAsKey('type')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(static function ($v): bool {
                                return !class_exists($v);
                            })
                            ->thenInvalid('Use fully qualified classnames as type values')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
