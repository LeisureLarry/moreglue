<?php

namespace MoreGlue\MVC\Controllers;

use \Monolog\Handler\FirePHPHandler, \Monolog\Logger;

class MasterController
{
    protected $_bootstrap;
    protected $_route;
    protected $_context;
    protected $_view;

    public function __construct($bootstrap, $router, $route)
    {
        $this->_bootstrap = $bootstrap;
        $this->_route = $route;

        // Init Logging
        $firephp = new FirePHPHandler();
        $logger = new Logger('LoggingChain');
        $logger->pushHandler($firephp);

        // Log Route Values
        $logger->addDebug('Route dispatch values', $route->dispatchValues());
        $logger->addDebug('Route wildcard values', $route->wildcardArgs());

        // Prefill Twig Context
        $this->_context = array(
            'router' => $router,
            'logger' => $logger
        );
    }

    public function getEm()
    {
        return $this->_bootstrap->getEm();
    }

    public function getTwig()
    {
        $srcPath = $this->_bootstrap->getOption('src_path');
        $libPath = dirname(__DIR__) . '/Views';

        $loader = new \Twig_Loader_Filesystem(
            array($libPath)
        );

        $loader->addPath(
            $srcPath . '/' . $this->_route->src . '/views',
            $this->_route->src
        );

        $twig = new \Twig_Environment($loader, array('debug' => $this->_bootstrap->isDebug()));
        $twig->addExtension(new \MoreGlue\Twig\Extension\RouteFilters());
        return $twig;
    }

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function getView()
    {
        return $this->_view . '.html.twig';
    }

    public function getTemplate()
    {
        $template = ucfirst($this->_route->controller) . '/' . $this->getView();
        $namespace = '@' . $this->_route->src;

        return $namespace . '/' . $template;
    }

    public function execute($actionName)
    {
        $this->setView($this->_route->action);
        $this->$actionName();
    }

    public function render()
    {
        echo $this->getTwig()->render(
            $this->getTemplate(),
            $this->_context
        );
    }
}
