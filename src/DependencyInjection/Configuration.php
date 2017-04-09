<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $rootNode    = ($builder = new TreeBuilder())->root('api_routing');
        $rootNode
            ->children()
            ->scalarNode('name')->defaultValue('php-api')
            ->end();

        return $builder;
    }
}
