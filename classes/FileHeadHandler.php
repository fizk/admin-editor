<?php

namespace Grav\Plugin\AdminEditor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;

class FileHeadHandler implements \Psr\Http\Server\RequestHandlerInterface
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

        $isFile = file_exists("$root$path");

        return new EmptyResponse($isFile ? 200 : 404);
    }
}