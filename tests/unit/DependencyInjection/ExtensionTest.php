<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\Tests\DependencyInjection;

use KleijnWeb\PhpApi\RoutingBundle\DependencyInjection\PhpApiRoutingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtensionTest extends TestCase
{
    /**
     * @var PhpApiRoutingExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new PhpApiRoutingExtension();
    }

    public function testHasRouteLoaderTag()
    {
        $container = new ContainerBuilder();
        $this->extension->load([], $container);
        self::assertTrue($container->hasDefinition('openapi.route_loader'));
        $routeLoader = $container->getDefinition('openapi.route_loader');
        self::assertSame([[]], $routeLoader->getTag('routing.loader'));
    }

    public function testHasAlias()
    {
        self::assertSame('api_routing', $this->extension->getAlias());
    }
}
