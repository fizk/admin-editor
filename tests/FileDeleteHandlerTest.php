<?php

use PHPUnit\Framework\TestCase;
use Grav\Plugin\AdminEditor\FileDeleteHandler;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Laminas\Diactoros\ServerRequest;

class FileDeleteHandlerTest extends TestCase
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


        $this->assertTrue(file_exists(vfsStream::url('root/pages/somefile.jpg')));

        $handler = new FileDeleteHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/somefile.jpg');

        $resonse = $handler->handle($request);
        $this->assertEquals(200, $resonse->getStatusCode());

        $this->assertFalse(file_exists(vfsStream::url('root/pages/somefile.jpg')));
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

        $handler = new FileDeleteHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/some-other-file.jpg');

        $resonse = $handler->handle($request);
        $this->assertEquals(404, $resonse->getStatusCode());
    }

    public function testDeleteFolder()
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
                'subpage' => [
                    'somefile.jpg' => 'data'
                ]
            ]
        ], $this->root);

        $handler = new FileDeleteHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/subpage');

        $this->assertTrue(is_dir(vfsStream::url('root/pages/subpage')));

        $resonse = $handler->handle($request);
        $this->assertEquals(200, $resonse->getStatusCode());

        $this->assertFalse(is_dir(vfsStream::url('root/pages/subpage')));
    }
}