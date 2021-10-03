<?php

use PHPUnit\Framework\TestCase;
use Grav\Plugin\AdminEditor\FileHeadHandler;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Laminas\Diactoros\ServerRequest;

class FileHeadHandlerTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function testResourceFound()
    {
        $locator = new class implements ResourceLocatorInterface {

            public function __invoke($uri)
            {
                return '';
            }

            public function isStream($uri)
            {
                return false;
            }

            public function findResource($uri, $absolute = true, $first = false)
            {
                return 'vfs://root/pages';
            }


            public function findResources($uri, $absolute = true, $all = false)
            {
                return 'vfs://root/pages';
            }
        };

        $this->root  = vfsStream::setup('root');
        $this->root = vfsStream::create([
            'pages' => [
                'somefile.jpg' => 'data'
            ]
        ], $this->root);

        $handler = new FileHeadHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/somefile.jpg');

        $resonse = $handler->handle($request);
        $this->assertEquals(200, $resonse->getStatusCode());
    }

    public function testResourceNotFound()
    {
        $locator = new class implements ResourceLocatorInterface {

            public function __invoke($uri)
            {
                return '';
            }

            public function isStream($uri)
            {
                return false;
            }

            public function findResource($uri, $absolute = true, $first = false)
            {
                return 'vfs://root/pages';
            }


            public function findResources($uri, $absolute = true, $all = false)
            {
                return 'vfs://root/pages';
            }
        };

        $this->root  = vfsStream::setup('root');
        $this->root = vfsStream::create([
            'pages' => [
                'somefile.jpg' => 'data'
            ]
        ], $this->root);

        $handler = new FileHeadHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/some-other-file.jpg');

        $resonse = $handler->handle($request);
        $this->assertEquals(404, $resonse->getStatusCode());
    }
}