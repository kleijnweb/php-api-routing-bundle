<?php declare(strict_types = 1);
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMetaTest extends TestCase
{
    public function testCreateFromRequestWillReturnNullWhenUriAttributeNotSet()
    {
        $request = new Request();
        $meta    = RequestMeta::fromRequest($request, new Repository());
        self::assertNull($meta);
    }

    public function testCanCreateFromRequest()
    {
        $request = $this->createRequest('/foo.yml', '/foo');
        $meta    = RequestMeta::fromRequest($request, $this->stubRepository());
        self::assertInstanceOf(RequestMeta::class, $meta);

        return $meta;
    }

    public function testWillReuseInstance()
    {
        $repository = $this->stubRepository();

        $request = $this->createRequest('/foo.yml', '/foo');
        $meta    = RequestMeta::fromRequest($request, $repository);

        self::assertSame($meta, RequestMeta::fromRequest($request, $repository));
    }

    /**
     * @depends testCanCreateFromRequest
     *
     * @param RequestMeta $meta
     */
    public function testCanUseGetters(RequestMeta $meta)
    {
        self::assertTrue(is_subclass_of($meta->getDescription(), Description::class));
        self::assertTrue(is_subclass_of($meta->getOperation(), Operation::class));
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

        $repository->expects(self::once())->method('get')->willReturn($description);
        $description->expects(self::once())->method('getPath')->willReturn($path);
        $path->expects(self::once())->method('getOperation')->willReturn($operation);

        return $repository;
    }
}
