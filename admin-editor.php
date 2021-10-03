<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Framework\Psr7\Response;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Plugin\AdminEditor;

/**
 * Class AdminEditorPlugin
 * @package Grav\Plugin
 */
class AdminEditorPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTwigTemplatePaths' => [
                ['onTwigTemplatePaths', -10]
            ],
            'onPluginsInitialized' => [
                // Uncomment following line when plugin requires Grav < 1.7
                // ['autoload', 100000],
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        // if ($this->isAdmin()) {
        //     return;
        // }

        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $config = $this->config();
        $route = $config['route'] ?? null;
        $regex = str_replace('/', '\/', $route);

        if (preg_match("/^{$regex}.*/", $uri->path())) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0]
            ]);
        }
    }

    public function onPageInitialized(): void
    {
        $config = $this->config();
        $baseUri = str_replace('/', '\/', $config['route'] ?? null);
        $mime = require __DIR__ . '/mime.php';

        $locator = $this->grav['locator'];
        $routes = [
            ["/^{$baseUri}$/", 'get', new AdminEditor\IndexHandler($locator, $config['route'] ?? null, $this->grav),],
            ["/^{$baseUri}\/(?<category>(pages|config|themes|accounts))((?<path>\/.+))?$/", 'get', new AdminEditor\FileReaderHandler($locator, $config['route'], $mime),],
            ["/^{$baseUri}\/(?<category>(pages|config|themes|accounts))((?<path>\/.+))?$/", 'put', new AdminEditor\FileWriterHandler($locator, $config['route']),],
            ["/^{$baseUri}\/(?<category>(pages|config|themes|accounts))((?<path>\/.+))?$/", 'head', new AdminEditor\FileHeadHandler($locator, $config['route']),],
            ["/^{$baseUri}\/(?<category>(pages|config|themes|accounts))((?<path>\/.+))?$/", 'delete', new AdminEditor\FileDeleteHandler($locator, $config['route']),],
        ];

        mb_parse_str($this->read_resource(fopen("php://input", "r")), $bodyQuery);

        $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $bodyQuery,
            $_COOKIE,
            $_FILES
        );

        $handlers = array_filter($routes, function ($route) use ($request) {
            return
                (preg_match($route[0], $request->getUri()->getPath()) === 1) &&
                (strtolower($request->getMethod()) === strtolower($route[1]));
        });

        $handlers = array_map(function ($item) use ($request) {
            preg_match($item[0], $request->getUri()->getPath(), $match);
            $item[] = $match;
            return $item;
        }, $handlers);

        if (count($handlers)) {
            $handler = array_pop($handlers);
            foreach($handler[3] as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
            $this->grav->close($handler[2]->handle($request));
        } else {
            $this->grav->close(new Response(
                404,
                ['Content-Type' => 'application/json'],
                json_encode(['message' => 'resource not found'])
            ));
        }
    }

    /**
     * Add template directory to twig lookup path.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    private function read_resource(/*resource*/$resource): ?string
    {
        $result = null;
        while ($data = fread($resource, 1024)) $result .= $data;
        fclose($resource);

        return $result;
    }
}


