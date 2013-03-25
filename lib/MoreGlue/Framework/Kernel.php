<?php

namespace MoreGlue\Framework;

use \DI\Container;
use \Webmasters\Doctrine\Bootstrap;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\Routing;
use \MoreGlue\Symfony\Component\Routing\Router;
use \MoreGlue\Symfony\Component\Routing\RouteMatches;
use \Monolog\Handler\FirePHPHandler;
use \Monolog\Logger;

class Kernel
{
    protected static $_singletonInstance = null;

    protected $_container;

    public static function getInstance(array $connectionOptions, array $applicationOptions)
    {
        if (self::$_singletonInstance === null) {
            self::$_singletonInstance = new Kernel($connectionOptions, $applicationOptions);
        }
        return self::$_singletonInstance;
    }

    protected function __construct($connectionOptions, $applicationOptions)
    {
        $bootstrap = Bootstrap::getInstance($connectionOptions, $applicationOptions);

        $this->addConfigurationEntries(array(
            'options.connection' => $connectionOptions,
            'options.application' => $bootstrap->getApplicationOptions(),
            'doctrine.bootstrap' => $bootstrap,
            'doctrine.cache' => function(Container $c)
            {
                return Kernel::getCache($c);
            },
            'doctrine.em' => function(Container $c)
            {
                return Kernel::getEntityManager($c);
            },
            'request' => function(Container $c)
            {
                return Kernel::getRequest($c);
            },
            'request.context' => function(Container $c)
            {
                return Kernel::getContext($c);
            },
            'router' => function(Container $c)
            {
                return Kernel::getRouter($c);
            },
            'router.routes' => function(Container $c)
            {
                return Kernel::getRoutes($c);
            },
            'router.matches' => function(Container $c)
            {
                return Kernel::getMatches($c);
            },
            'router.generator.url' => function(Container $c)
            {
                return Kernel::getUrlGenerator($c);
            },
            'logger' => function(Container $c)
            {
                return Kernel::getLogger($c);
            },
            'twig' => function(Container $c)
            {
                return Kernel::getTwig($c);
            }
        ));
    }

    protected function __clone()
    {
    }

    public function getContainer()
    {
        return Container::getInstance();
    }

    public function addConfigurationEntries($configuration)
    {
        Container::addConfiguration(array('entries' => $configuration));
    }

    public static function getCache($c)
    {
        return $c['doctrine.bootstrap']->getCache();
    }

    public static function getEntityManager($c)
    {
        return $c['doctrine.bootstrap']->getEm();
    }

    public static function getRequest($c)
    {
        $request = Request::createFromGlobals();

        if (!$request->hasPreviousSession()) {
            $session = new Session();
            $session->start();
            $request->setSession($session);
        }

        return $request;
    }

    public static function getContext($c)
    {
        $context = new Routing\RequestContext();
        $context->fromRequest($c['request']);
        return $context;
    }

    public static function getRouter($c)
    {
        $src =  $c['options.application']->get('src'); // TODO: automatic
        foreach ($src as $bundle) {
            $dirs[] = $c['options.application']->get('src_dir') . '/Bundles/' . $bundle . '/Controllers';
        }

        $router = new Router(
            $c['doctrine.bootstrap']->getAnnotationReader(),
            $dirs,
            array(),
            $c['request.context']
        );

        return $router;
    }

    public static function getRoutes($c)
    {
        return $c['router']->getRouteCollection();
    }

    public static function getMatches($c)
    {
        static $routeMatches;

        if ($routeMatches === null) {
            $matches = $c['router']->match($c['request']->getPathInfo());
            $routeMatches = RouteMatches::getInstance($matches);
        }

        return $routeMatches;
    }

    public static function getUrlGenerator($c)
    {
        return new Routing\Generator\UrlGenerator($c['router.routes'], $c['request.context']);
    }

    public static function getLogger($c)
    {
        static $logger;

        if (empty($logger)) {
            $firephp = new FirePHPHandler();
            $logger = new Logger('Monolog Logger');
            $logger->pushHandler($firephp);
        }

        return $logger;
    }

    public static function getTwig($c)
    {
        static $twig;

        if ($twig === null) {
            $srcDir = $c['options.application']->get('src_dir');
            $templateDir = __DIR__ . '/MVC/Views/Templates';

            $isDebug = $c['doctrine.bootstrap']->isDebug();

            $loader = new \Twig_Loader_Filesystem(
                array($templateDir)
            );

            $src =  $c['options.application']->get('src'); // TODO: automatic
            foreach ($src as $bundle) {
                $bundleViews = $srcDir . '/Bundles/' . $bundle . '/Templates';
                $loader->addPath(
                    $bundleViews,
                    $bundle
                );
            }

            $twig = new \Twig_Environment($loader, array('debug' => $isDebug));

            $twig->addExtension(
                new \MoreGlue\Twig\Extension\RequestExtension($c['request'])
            );

            $twig->addExtension(
                new \Symfony\Bridge\Twig\Extension\RoutingExtension($c['router.generator.url'])
            );

            if ($isDebug) {
                $twig->addExtension(
                    new \Twig_Extension_Debug()
                );
            }
        }

        return $twig;
    }
}
