<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\Tests\Routing;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMetaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createFromRequestWillReturnNullWhenUriAttributeNotSet()
    {
        $request = new Request();
        $meta    = RequestMeta::fromRequest($request, new Repository());
        $this->assertNull($meta);
    }

    /**
     * @test
     */
    public function canCreateFromRequest()
    {
        $request = $this->createRequest('/foo.yml', '/foo');
        $meta    = RequestMeta::fromRequest($request, $this->stubRepository());
        $this->assertInstanceOf(RequestMeta::class, $meta);

        return $meta;
    }

    /**
     * @test
     */
    public function willReuseInstance()
    {
        $repository = $this->stubRepository();

        $request = $this->createRequest('/foo.yml', '/foo');
        $meta    = RequestMeta::fromRequest($request, $repository);

        $this->assertSame($meta, RequestMeta::fromRequest($request, $repository));
    }

    /**
     * @test
     */
    public function canUseGetters()
    {
        $meta = $this->canCreateFromRequest();
        $this->assertTrue(is_subclass_of($meta->getDescription(), Description::class));
        $this->assertTrue(is_subclass_of($meta->getOperation(), Operation::class));
    }

    /**
     * @param string $uri
     * @param string $path
     * @return Request
     */
    private function createRequest(string $uri, string $path): Request
    {
        return new Request([], [], [RequestMeta::ATTRIBUTE_URI => $uri, RequestMeta::ATTRIBUTE_PATH => $path]);
    }

    /**
     * @return Repository
     */
    private function stubRepository(): Repository
    {
        $repository  = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();
        $description = $this->getMockBuilder(Description::class)->disableOriginalConstructor()->getMock();
        $path        = $this->getMockBuilder(Path::class)->disableOriginalConstructor()->getMock();
        $operation   = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();

        $repository->expects($this->once())->method('get')->willReturn($description);
        $description->expects($this->once())->method('getPath')->willReturn($path);
        $path->expects($this->once())->method('getOperation')->willReturn($operation);
        /** @var Repository $repository */
        return $repository;
    }
}
