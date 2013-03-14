<?php

namespace MoreGlue;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\Routing;
use \Webmasters\Doctrine\Bootstrap;

class Framework
{
    protected $_bootstrap;
    protected $_context;
    protected $_matcher;
    protected $_matches;

    public function __construct($bootstrap)
    {
        $this->_bootstrap = $bootstrap;
        $this->_request = Request::createFromGlobals();
    }

    public function getBootstrap()
    {
        return $this->_bootstrap;
    }

    public function getSourcePath()
    {
        return $this->getBootstrap()->getOption('src_path');
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
        if (empty($this->_context)) {
            $context = new Routing\RequestContext();
            $context->fromRequest($this->_request);
            $this->_context = $context;
        }

        return $this->_context;
    }

    public function getMatches()
    {
        return $this->_matches;
    }

    public function getTwig()
    {
        $srcPath = $this->getSourcePath();
        $libPath = __DIR__ . '/MVC/Views';

        $isDebug = $this->_bootstrap->isDebug();

        $loader = new \Twig_Loader_Filesystem(
            array($libPath)
        );

        $src = $this->getBootstrap()->getOption('src');
        foreach ($src as $app) {
            $loader->addPath(
                $srcPath . '/' . $app . '/Views',
                $app
            );
        }

        $twig = new \Twig_Environment($loader, array('debug' => $isDebug));

        // Activate RoutingExtension from "symfony/twig-bridge"
        $generator = new Routing\Generator\UrlGenerator($this->getRoutes(), $this->getContext());
        $twig->addExtension(
            new \Symfony\Bridge\Twig\Extension\RoutingExtension($generator)
        );

        if ($isDebug) {
            $twig->addExtension(
                new \Twig_Extension_Debug()
            );
        }

        return $twig;
    }

    public function match()
    {
        $matches = $this->_matcher->match($this->_request->getPathInfo());
        $this->_matches = $matches;

        $controllerName = ucfirst($matches['controller']) . 'Controller';
        $actionName = ucfirst($matches['action']) . 'Action';

        $srcPath = $this->getSourcePath();
        $controllerFile = $srcPath . '/' . ucfirst($matches['src']) . '/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            throw new Routing\Exception\ResourceNotFoundException(
                sprintf("Controller file '%s' missing", $controllerFile)
            );
        }

        $namespacedControllerName = ucfirst($matches['src']) . '\\Controllers\\' . $controllerName;

        if (class_exists($namespacedControllerName, false)) {
            $controller = new $namespacedControllerName($this);
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

    public function dispatch()
    {
        $this->_matcher = new Routing\Matcher\UrlMatcher($this->getRoutes(), $this->getContext());

        try {
            $output = $this->match();
            $response = new Response($output);
        } catch (Routing\Exception\ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (Exception $e) {
            $response = new Response('An error occurred', 500);
        }

        return $response;
    }
}
