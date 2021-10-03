<?php

use PHPUnit\Framework\TestCase;
use Grav\Plugin\AdminEditor\FileWriterHandler;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;

class FileWriterHandlerTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function testCreateDirectorySuccess()
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

        $handler = new FileWriterHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/new-dir')
            ->withHeader('content-type', 'text/directory');

        $resonse = $handler->handle($request);
        $this->assertEquals(201, $resonse->getStatusCode());

        $this->assertTrue(is_dir(vfsStream::url('root/pages/new-dir')));
    }

    public function testCreateDirectoryConflict()
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
                'somefile.jpg' => 'data',
                'new-dir' => []
            ]
        ], $this->root);

        $handler = new FileWriterHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/new-dir')
            ->withHeader('content-type', 'text/directory');

        $resonse = $handler->handle($request);
        $this->assertEquals(409, $resonse->getStatusCode());
    }

    public function testCreateFileSuccess()
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
            'pages' => []
        ], $this->root);

        $handler = new FileWriterHandler($locator, '');
        $request = (new ServerRequest())
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/new-file.txt')
            ->withHeader('content-type', 'text/plain');

        $resonse = $handler->handle($request);
        $this->assertEquals(201, $resonse->getStatusCode());

        $this->assertTrue(is_file(vfsStream::url('root/pages/new-file.txt')));
    }

    public function testUpdateFileSuccess()
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
                'new-file.txt' => 'old-data'
            ]
        ], $this->root);

        $handler = new FileWriterHandler($locator, '');
        $request = (new ServerRequest([],[], null, null, (new StreamFactory())->createStream('new-data')))
            ->withAttribute('category', 'pages')
            ->withAttribute('path', '/new-file.txt')
            ->withHeader('content-type', 'text/plain');

        $resonse = $handler->handle($request);
        $this->assertEquals(204, $resonse->getStatusCode());

        $this->assertTrue(is_file(vfsStream::url('root/pages/new-file.txt')));
        $this->assertEquals('new-data', file_get_contents(vfsStream::url('root/pages/new-file.txt')));
    }
}