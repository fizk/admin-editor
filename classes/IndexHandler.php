<?php

namespace Grav\Plugin\AdminEditor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use Grav\Common\Grav;
use Laminas\Diactoros\Response\HtmlResponse;

class IndexHandler implements \Psr\Http\Server\RequestHandlerInterface
{
    private ResourceLocatorInterface $locator;
    private Grav $grav;
    private ?string $route;

    public function __construct(ResourceLocatorInterface $locator, ?string $route, Grav $grav)
    {
        $this->locator = $locator;
        $this->grav = $grav;
        $this->route = $route;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /** @var \Grav\Common\Twig\Twig */
        $twig = $this->grav['twig'];

        $uri = $this->grav['uri'];
        $baseUrlRelative = $uri->rootUrl(false);

        $data = [
            'admin_editor_url' => $baseUrlRelative . '/user/plugins/admin-editor',
        ];

        return new HtmlResponse(
            $twig->twig()->render('base.html.twig', $data),
            200
        );

        // print_r($this->grav['twig']->twig_paths);
        // die;
        // return new JsonResponse([
        //     $this->locator->findResource("user://pages") => $this->route . str_replace($this->locator->getBase().'/user', '', $this->locator->findResource("user://pages")),
        //     $this->locator->findResource("user://config") => $this->route . str_replace($this->locator->getBase().'/user', '', $this->locator->findResource("user://config")),
        //     $this->locator->findResource("user://accounts") => $this->route . str_replace($this->locator->getBase().'/user', '', $this->locator->findResource("user://accounts")),
        //     $this->locator->findResource("user://themes") => $this->route . str_replace($this->locator->getBase().'/user', '', $this->locator->findResource("user://themes")),
        // ], 200);
    }
}