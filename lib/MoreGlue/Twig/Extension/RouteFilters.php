<?php

namespace MoreGlue\Twig\Extension;

use \Monolog\Handler\FirePHPHandler, \Monolog\Logger;

class RouteFilters extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('reverseRoute', array($this, 'reverseRoute')),
            new \Twig_SimpleFilter('reversePublic', array($this, 'reversePublic')),
        );
    }

    public function reverseRoute($router, $src, $controller = null, $action = null, $id = null)
    {
        $routeParams = array('src' => $src);

        $routeParams['controller'] = 'index';
        if (!empty($controller)) {
            $routeParams['controller'] = $controller;
        }

        $routeParams['action'] = 'index';
        if (!empty($action)) {
            $routeParams['action'] = $action;
        }

        if (!empty($id)) {
            $routeParams['id'] = $id;
        }

        $reverseRoute = $router->reverseRoute($routeParams);

        // Init Logging
        $firephp = new FirePHPHandler();
        $logger = new Logger('LoggingChain');
        $logger->pushHandler($firephp);

        // Log Route Values
        $logger->addDebug('Reverse route', (array)$reverseRoute);

        return $reverseRoute;
    }

    public function reversePublic($router, $src = '', $folder = '')
    {
        return $router->reverseRoute(
            array('src' => $src, 'folder' => $folder)
        );
    }

    public function getName()
    {
        return 'MoreGlue\RouteFilters';
    }
}