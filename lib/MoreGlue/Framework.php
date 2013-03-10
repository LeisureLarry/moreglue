<?php

namespace MoreGlue;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\Routing;
use \Webmasters\Doctrine\Bootstrap;

class Framework
{
    protected $_bootstrap;
    protected $_srcPath;
    protected $_matcher;

    public function __construct()
    {
        $this->_bootstrap = Bootstrap::getInstance();
        $this->_srcPath = $this->_bootstrap->getOption('src_path');
        $this->_request = Request::createFromGlobals();
    }

    public function getRoutes()
    {
        $routes = new Routing\RouteCollection();

        $routes->add(
            'backend',
            new Routing\Route(
                '/admin/{controller}/{action}/{id}',
                array('src' => 'backend', 'controller' => 'index', 'action' => 'index', 'id' => null)
            )
        );
        $routes->add(
            'frontend',
            new Routing\Route(
                '/{controller}/{action}/{id}',
                array('src' => 'frontend', 'controller' => 'index', 'action' => 'index', 'id' => null)
            )
        );

        return $routes;
    }

    public function getContext()
    {
        $context = new Routing\RequestContext();
        $context->fromRequest($this->_request);

        return $context;
    }

    public function dispatch()
    {
        $this->_matcher = new Routing\Matcher\UrlMatcher($this->getRoutes(), $this->getContext());

        try {
            $matches = $this->_matcher->match($this->_request->getPathInfo());

            ob_start();
            $output = $this->execute($matches);
            $response = new Response($output);
        } catch (Routing\Exception\ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (Exception $e) {
            $response = new Response('An error occurred', 500);
        }

        $response->send();
    }

    public function execute($matches)
    {
        $controllerName = ucfirst($matches['controller']) . 'Controller';
        $actionName = ucfirst($matches['action']) . 'Action';

        $controllerFile = $this->_srcPath . '/' . ucfirst($matches['src']) . '/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            throw new Routing\Exception\ResourceNotFoundException(
                sprintf("Controller file '%s' missing", $controllerFile)
            );
        }

        $namespacedControllerName = ucfirst($matches['src']) . '\\Controllers\\' . $controllerName;

        if (class_exists($namespacedControllerName, false)) {
            $controller = new $namespacedControllerName($this->_bootstrap, $this->_matcher, $matches);
        } else {
            throw new Routing\Exception\ResourceNotFoundException(
                sprintf("Controller class '%s' missing", $namespacedControllerName)
            );
        }

        if (method_exists($namespacedControllerName, $actionName)) {
            $output = $controller->execute($actionName);
        } else {
            throw new Routing\Exception\ResourceNotFoundException(
                sprintf("Action method '%s' missing in controller class '%s'", $actionName, $namespacedControllerName)
            );
        }

        return $output;
    }
}
