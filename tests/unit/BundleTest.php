<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\Tests\DependencyInjection;

use KleijnWeb\PhpApi\RoutingBundle\DependencyInjection\PhpApiRoutingExtension;
use KleijnWeb\PhpApi\RoutingBundle\PhpApiRoutingBundle;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
    public function testCanGetExtension()
    {
        $routingBundle = new PhpApiRoutingBundle();
        self::assertInstanceOf(PhpApiRoutingExtension::class, $routingBundle->getContainerExtension());
    }

    public function testCanGetNamespace()
    {
        $routingBundle = new PhpApiRoutingBundle();
        self::assertSame('KleijnWeb\PhpApi\RoutingBundle', $routingBundle->getNamespace());
    }
}
