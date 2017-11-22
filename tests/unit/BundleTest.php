<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\Tests\DependencyInjection;

use KleijnWeb\PhpApi\RoutingBundle\DependencyInjection\PhpApiRoutingExtension;
use KleijnWeb\PhpApi\RoutingBundle\PhpApiRoutingBundle;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canGetExtension()
    {
        $routingBundle = new PhpApiRoutingBundle();
        $this->assertInstanceOf(PhpApiRoutingExtension::class, $routingBundle->getContainerExtension());
    }

    /**
     * @test
     */
    public function canGetNamespace()
    {
        $routingBundle = new PhpApiRoutingBundle();
        $this->assertSame('KleijnWeb\PhpApi\RoutingBundle', $routingBundle->getNamespace());
    }
}
