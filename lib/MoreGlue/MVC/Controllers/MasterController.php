<?php

namespace MoreGlue\MVC\Controllers;

use \Monolog\Handler\FirePHPHandler, \Monolog\Logger;

class MasterController
{
    protected $_bootstrap;
    protected $_matches;
    protected $_logger;
    protected $_view;
    protected $_context = array();

    public function __construct($bootstrap, $matcher, $matches)
    {
        $this->_bootstrap = $bootstrap;
        $this->_matches = $matches;
        $this->_logger = $this->_initLogging();
    }

    protected  function _initLogging()
    {
        $firephp = new FirePHPHandler();
        $logger = new Logger('Monolog Logger');
        $logger->pushHandler($firephp);

        return $logger;
    }

    public function logDebug($message, $context)
    {
        $this->_logger->addDebug($message, (array)$context);
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
            $srcPath . '/' . ucfirst($this->_matches['src']) . '/Views',
            ucfirst($this->_matches['src'])
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
        $template = ucfirst($this->_matches['controller']) . '/' . $this->getView();
        $namespace = '@' . ucfirst($this->_matches['src']);

        return $namespace . '/' . $template;
    }

    public function execute($actionName)
    {
        $this->setView($this->_matches['action']);
        $this->$actionName();

        return $this->render();
    }

    public function render()
    {
        return $this->getTwig()->render(
            $this->getTemplate(),
            $this->_context
        );
    }
}
