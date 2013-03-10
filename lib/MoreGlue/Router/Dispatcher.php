<?php

namespace MoreGlue\Router;

use \Kunststube\Router\Router, \Kunststube\Router\Route, \Kunststube\Router\NotFoundException;
use \Webmasters\Doctrine\Bootstrap;

class Dispatcher
{
    protected $_bootstrap;
    protected $_router;
    protected $_srcPath;

    public function __construct($router)
    {
        $this->_bootstrap = Bootstrap::getInstance();
        $this->_router = $router;
        $this->_srcPath = $this->_bootstrap->getOption('src_path');
    }

    public function defaultCallback(Route $route)
    {
        $controllerName = ucfirst($route->controller) . 'Controller';

        if ($route->action == '') $route->action = 'index';
        $actionName = ucfirst($route->action) . 'Action';

        $controllerFile = $this->_srcPath . '/' . $route->src . '/controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            throw new NotFoundException(
                sprintf("Controller file '%s' missing", $controllerFile)
            );
        }

        $namespacedControllerName = $route->src . '\\Controllers\\' . $controllerName;

        if (class_exists($namespacedControllerName, false)) {
            $controller = new $namespacedControllerName($this->_bootstrap, $this->_router, $route);
        } else {
            throw new NotFoundException(
                sprintf("Controller class '%s' missing", $namespacedControllerName)
            );
        }

        if (method_exists($namespacedControllerName, $actionName)) {
            $controller->execute($actionName);
        } else {
            throw new NotFoundException(
                sprintf("Action method '%s' missing in controller class '%s'", $actionName, $namespacedControllerName)
            );
        }
    }

    public function dispatch($query)
    {
        $this->_router->defaultCallback(array($this, 'defaultCallback'));

        if (!preg_match('/\/$/', $query)) {
            $query .= '/';
        }

        try {
            $this->_router->route($query);
        } catch (NotFoundException $e) {
            $error404 = 'Error 404';
            if ($this->_bootstrap->isDebug()) {
                $error404 = $e->getMessage();
            }
            die($error404);
        }
    }
}
