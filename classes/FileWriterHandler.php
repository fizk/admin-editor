<?php

namespace Grav\Plugin\AdminEditor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\EmptyResponse;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use SplFileInfo;

class FileWriterHandler implements \Psr\Http\Server\RequestHandlerInterface
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

        if ($request->getHeader('content-type')[0] === 'text/directory') {

            if (is_dir("$root$path")) {
                return new EmptyResponse(409);
            }

            if (!@mkdir("$root$path", 0777, true)) {
                return new EmptyResponse(400);
            }

        } else {
            //TODO check is extension is permitted.
            $resource = fopen("$root$path", 'w');
            $request->getBody()->rewind();
            while ($data = $request->getBody()->read(1024)) {
                fwrite($resource, $data);
            }
            fclose($resource);
        }

        $location = $this->locator->findResource("user://{$category}{$path}");
        $categoryPosition = strpos($location, $category);
        return new JsonResponse(
            $this->fileInfo(new SplFileInfo($location), $category, $this->route),
            $isFile ? 204 : 201,
            [
                'Location' => "{$this->route}/" . substr($location, $categoryPosition),
            ]
        );
    }

    private function fileInfo(SplFileInfo $file, string $category, string $route): array
    {
        $categoryPosition = strpos($file->getPathname(), $category);
        return [
            'type' => $file->getType(),
            'name' => $file->getFilename(),
            'url' => "{$route}/" . substr($file->getPathname(), $categoryPosition),
            'category' => $category,
            'created' => $file->getCTime(),
            'modified' => $file->getMTime(),
            'extension' => $file->getExtension(),
            'permissions' => substr(sprintf('%o', $file->getPerms()), -4),
            'owner' => $file->getOwner(),
            'group' => $file->getGroup(),
            'size' => $file->getSize(),
        ];
    }
}