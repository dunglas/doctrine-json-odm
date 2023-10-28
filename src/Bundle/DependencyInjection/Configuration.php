<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dunglas_doctrine_json_odm');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('type_map')
                    ->defaultValue([])
                    ->useAttributeAsKey('type')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(static function (string $v): bool {
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
