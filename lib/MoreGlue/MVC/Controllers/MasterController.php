<?php

namespace MoreGlue\MVC\Controllers;

use \Monolog\Handler\FirePHPHandler, \Monolog\Logger;

class MasterController
{
    protected $_framework;
    protected $_logger;
    protected $_view;
    protected $_context = array();

    public function __construct($framework)
    {
        $this->_framework = $framework;
        $this->_initLogging();
    }

    protected  function _initLogging()
    {
        $firephp = new FirePHPHandler();
        $this->_logger = new Logger('Monolog Logger');
        $this->_logger->pushHandler($firephp);
    }

    public function getFramework()
    {
        return $this->_framework;
    }

    public function getBootstrap()
    {
        return $this->getFramework()->getBootstrap();
    }

    public function getEm()
    {
        return $this->getBootstrap()->getEm();
    }

    public function getMatches()
    {
        return $this->getFramework()->getMatches();
    }

    public function getTwig()
    {
        return $this->getFramework()->getTwig();
    }

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function getView()
    {
        return $this->_view . '.html.twig';
    }

    public function addContext($name, $value)
    {
        $this->_context[$name] = $value;
    }

    public function getContext()
    {
        return $this->_context;
    }

    public function getTemplate()
    {
        $matches = $this->getMatches();

        $template = ucfirst($matches['controller']) . '/' . $this->getView();
        $namespace = '@' . ucfirst($matches['src']);

        return $namespace . '/' . $template;
    }

    public function logDebug($message, $context)
    {
        $this->_logger->addDebug($message, (array)$context);
    }

    public function execute($actionName)
    {
        $matches = $this->getMatches();

        $this->setView($matches['action']);
        $this->$actionName();

        return $this->render();
    }

    public function render()
    {
        return $this->getTwig()->render(
            $this->getTemplate(),
            $this->getContext()
        );
    }
}
