<?php

namespace Grav\Plugin\AdminEditor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use SplFileInfo;

class FileDeleteHandler implements \Psr\Http\Server\RequestHandlerInterface
{
    private ResourceLocatorInterface $locator;
    private string $route;

    public function __construct(ResourceLocatorInterface $locator, string $route)
    {
        $this->locator = $locator;
        $this->route = $route;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $category = $request->getAttribute('category');
        $path = $request->getAttribute('path');
        $root = $this->locator->findResource("user://{$category}");
        $realPath = "$root$path";

        $isFile = file_exists($realPath);

        if (!$isFile) {
            return new EmptyResponse(404);
        }

        is_dir($realPath)
            ? $this->rrmdir($realPath)
            : unlink($realPath);

        return new EmptyResponse(200);

    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }
}