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
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\RoutingBundle\Routing\OpenApiRouteLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiRouteLoaderTest extends TestCase
{
    const DOCUMENT_PATH = '/totally/non-existent/path';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $descriptionMock;

    /**
     * @var OpenApiRouteLoader
     */
    private $loader;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->descriptionMock = $this
            ->getMockBuilder(Description::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock
            ->expects(self::any())
            ->method('get')
            ->willReturn($this->descriptionMock);

        $this->loader = new OpenApiRouteLoader($repository, 'customname');
    }

    public function testSupportSwaggerAsRouteTypeOnly()
    {
        self::assertFalse($this->loader->supports('/a/b/c'));
        self::assertTrue($this->loader->supports('/a/b/c', 'customname'));
    }

    public function testCanLoadMultipleDocuments()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([]);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH.'2');
    }

    public function testLoadingMultipleDocumentsWillPreventRouteKeyCollisions()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn(
                [
                    new Path('/a', [new Operation('', '/a', 'get')]),
                ]
            );

        $routes1 = $this->loader->load(self::DOCUMENT_PATH);
        $routes2 = $this->loader->load(self::DOCUMENT_PATH.'2');
        self::assertSame(count($routes1), count(array_diff_key($routes1->all(), $routes2->all())));
    }

    public function testCannotLoadSameDocumentMoreThanOnce()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([]);

        self::expectException(\RuntimeException::class);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH);
    }

    public function testWillReturnRouteCollection()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        self::assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
    }

    public function testRouteCollectionWillContainOneRouteForEveryPathAndMethod()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn(
                [
                    new Path('/a', [new Operation(uniqid(), '/a', 'get'), new Operation(uniqid(), '/a', 'post')]),
                    new Path('/b', [new Operation(uniqid(), '/b', 'get')]),
                ]
            );

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        self::assertCount(3, $routes);
    }

    public function testShouldCreateRoutesWithTheCorrectHttpSchemes()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [
                    new Operation(uniqid(), '/a', 'get'),
                    new Operation(uniqid(), '/a', 'post'),
                ]),
            ]);

        $this->descriptionMock
            ->expects(self::any())
            ->method('getSchemes')
            ->willReturn(['https', 'http']);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        self::assertCount(2, $routes);

        foreach ($routes as $route) {
            self::assertEquals(['https', 'http'], $route->getSchemes());
        }
    }

    public function testRouteCollectionWillIncludeSeparateRoutesForSubPaths()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn(
                [
                    new Path('/a', [new Operation(uniqid(), '/a', 'get')]),
                    new Path('/a/b', [new Operation(uniqid(), '/a/b', 'get')]),
                    new Path('/a/b/c', [new Operation(uniqid(), '/a/b/c', 'get')]),
                ]
            );


        $routes = $this->loader->load(self::DOCUMENT_PATH);

        self::assertCount(3, $routes);
    }

    public function testCanUseOperationIdAsControllerKey()
    {
        $expected = 'my.controller.key:methodName';

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([
                new Path(
                    '/a',
                    [new Operation('/a:get', '/a', 'get'), new Operation($expected, '/a', 'post'),]
                ),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('customname.path.a.methodName');
        self::assertNotNull($actual);
        self::assertSame($expected, $actual->getDefault('_controller'));
    }

    public function testCanUseXRouterMethodToOverrideMethod()
    {
        $extensions = ['router-controller-method' => 'myMethodName'];

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([
                new Path(
                    '/a',
                    [
                        new Operation('/a:get', '/a', 'get'),
                        new Operation('/a:post', '/a', 'post', [], null, [], $extensions),
                    ]
                ),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('customname.path.a.myMethodName');
        self::assertNotNull($actual);
    }

    public function testCanUseXRouterControllerForDiKeyInOperation()
    {
        $diKey      = 'my.x_router.controller';
        $expected   = "$diKey:post";
        $extensions = ['router-controller' => $diKey];
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([
                new Path(
                    '/a',
                    [
                        new Operation('/a:get', '/a', 'get'),
                        new Operation('/a:post', '/a', 'post', [], null, [], $extensions),
                    ]
                ),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('customname.path.a.post');
        self::assertNotNull($actual);
        self::assertSame($expected, $actual->getDefault('_controller'));
    }

    public function testCanUseXRouterControllerForDiKeyInPath()
    {
        $diKey    = 'my.x_router.controller';
        $expected = "$diKey:post";
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:post', '/a', 'post')])]);

        $this->descriptionMock
            ->expects(self::atLeast(1))
            ->method('getExtension')
            ->willReturnCallback(
                function (string $name) use ($diKey) {
                    return $name == 'router-controller' ? $diKey : null;
                }
            );

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('customname.path.a.post');
        self::assertNotNull($actual);
        self::assertSame($expected, $actual->getDefault('_controller'));
    }

    public function testCanUseXRouterForDiKeyInPath()
    {
        $router   = 'my.x_router';
        $expected = "$router.a:post";
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:post', '/a', 'post')])]);

        $this->descriptionMock
            ->expects(self::atLeast(1))
            ->method('getExtension')
            ->willReturnCallback(
                function (string $name) use ($router) {
                    return $name == 'router' ? $router : null;
                }
            );

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('customname.path.a.post');
        self::assertNotNull($actual);
        self::assertSame($expected, $actual->getDefault('_controller'));
    }

    public function testRouteCollectionWillIncludeSeparateRoutesForSubPathMethodCombinations()
    {
        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([
                new Path(
                    '/a',
                    [new Operation('/a:get', '/a', 'get')]
                ),
                new Path(
                    '/a/b',
                    [new Operation('/a/b:get', '/a/b', 'get'), new Operation('/a/b:post', '/a/b', 'post')]
                ),
                new Path('/a/b/c', [new Operation('/a/b/c:get', '/a/b/c', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        self::assertCount(4, $routes);
    }

    public function testRouteCollectionWillContainPathFromDescription()
    {
        $paths = [
            new Path('/a', [new Operation('/a:get', '/a', 'get'),]),
            new Path('/a/b', [new Operation('/a/b:get', '/a/b', 'get'),]),
            new Path('/a/b/c', [new Operation('/a/b/c:get', '/a/b/c', 'get')]),
            new Path('/d/f/g', [new Operation('/d/f/g:get', '/d/f/g', 'get')]),
            new Path('/1/2/3', [new Operation('/1/2/3:get', '/1/2/3', 'get')]),
            new Path('/foo/{bar}/{blah}', [new Operation('/foo/{bar}/{blah}:get', '/foo/{bar}/{blah}', 'get')]),
            new Path('/z', [new Operation('/z:get', '/z', 'get'),]),
        ];

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn($paths);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $descriptionPaths = array_map(
            function (Path $path) {
                return $path->getPath();
            },
            $paths
        );
        sort($descriptionPaths);

        $routePaths = array_map(
            function (Route $route) {
                return $route->getPath();
            },
            $routes->getIterator()->getArrayCopy()
        );

        sort($routePaths);
        self::assertSame($descriptionPaths, $routePaths);
    }

    public function testWillAddRequirementsForIntegerPathParams()
    {
        $parameter = new Parameter(
            'foo',
            true,
            new ScalarSchema((object)['type' => Schema::TYPE_INT]),
            Parameter::IN_PATH
        );

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('customname.path.a.get');
        self::assertNotNull($actual);
        $requirements = $actual->getRequirements();
        self::assertNotNull($requirements);

        self::assertSame($requirements['foo'], '\d+');
    }

    public function testWillAddRequirementsForStringPatternParams()
    {
        $expected  = '\d{2}hello';
        $parameter = new Parameter(
            'aString',
            true,
            new ScalarSchema(
                (object)[
                    'type'    => Schema::TYPE_STRING,
                    'pattern' => $expected,
                ]
            ),
            Parameter::IN_PATH
        );

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('customname.path.a.get');
        self::assertNotNull($actual);
        $requirements = $actual->getRequirements();
        self::assertNotNull($requirements);

        $this->assertSame($expected, $requirements['aString']);
    }

    public function testWillAddRequirementsForStringEnumParams()
    {
        $enum      = ['a', 'b', 'c'];
        $expected  = '(a|b|c)';
        $parameter = new Parameter(
            'aString',
            true,
            new ScalarSchema(
                (object)[
                    'type' => Schema::TYPE_STRING,
                    'enum' => $enum,
                ]
            ),
            Parameter::IN_PATH
        );

        $this->descriptionMock
            ->expects(self::any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('customname.path.a.get');
        self::assertNotNull($actual);
        $requirements = $actual->getRequirements();
        self::assertNotNull($requirements);

        self::assertSame($expected, $requirements['aString']);
    }
}
