<?php

namespace Grav\Plugin\AdminEditor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use UnexpectedValueException;
use SplFileInfo;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Throwable;

class FileReaderHandler implements \Psr\Http\Server\RequestHandlerInterface
{
    private ResourceLocatorInterface $locator;
    private string $route;
    private array $mime;

    public function __construct(ResourceLocatorInterface $locator, string $route, array $mime = [])
    {
        $this->locator = $locator;
        $this->route = $route;
        $this->mime = $mime;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $category = $request->getAttribute('category');
        $path = $request->getAttribute('path');
        $basePath = $this->locator->findResource("user://{$category}" . ($path ? '/' . $path : ''));

        $fileInfo = new SplFileInfo($basePath);

        if (!$fileInfo->isReadable()) {
            return new EmptyResponse(404);
        }

        if ($fileInfo->isFile()) {
            return new Response(
                new Stream($fileInfo->getRealPath()),
                200,
                ['content-type' => $this->getMime($fileInfo->getExtension())]
            );
        }

        try {
            return new JsonResponse($this->traverse($basePath, $category, $this->route));
        } catch (UnexpectedValueException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    private function fileInfo(SplFileInfo $file, string $category, string $route): array
    {
        $categoryPosition = strpos($file->getPathname(), $category);
        return [
            'type' => $file->getType(),
            'name' => $file->getFilename(),
            'url' => "{$route}/" . substr($file->getPathname(), $categoryPosition),
            'category' => $category,
            'created' =>$file->getCTime(),
            'modified' =>$file->getMTime(),
            'extension' => $file->getExtension(),
            'permissions' => substr(sprintf('%o', $file->getPerms()), -4),
            'owner' => $file->getOwner(),
            'group' => $file->getGroup(),
            'size' => $file->getSize(),
        ];
    }

    private function traverse(string $path, string $category, string $route): array
    {
        $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

        $result = [];
        foreach($iterator as $child) {
            $childInfo = $this->fileInfo($child, $category, $route);
            if ($child->isDir()) {
                $childInfo['children'] = $this->traverse($child->getRealPath(), $category, $route);
            }
            $result[] = $childInfo;
        }

        return $result;
    }

    private function getMime(string $ext): string
    {
        foreach ($this->mime as $key => $value) {
            if (in_array(strtolower($ext), $value)) {
                return $key;
            }
        }

        return 'application/octet-stream';
    }
}