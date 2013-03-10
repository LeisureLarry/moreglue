<?php

namespace MoreGlue\MVC\Controllers;

use \Monolog\Handler\FirePHPHandler, \Monolog\Logger;

class MasterController
{
    protected $_bootstrap;
    protected $_twig;
    protected $_logger;
    protected $_view;
    protected $_context = array();

    public function __construct($bootstrap, $twig)
    {
        $this->_bootstrap = $bootstrap;
        $this->_twig = $twig;

        $this->_initLogging();
    }

    protected  function _initLogging()
    {
        $firephp = new FirePHPHandler();
        $this->_logger = new Logger('Monolog Logger');
        $this->_logger->pushHandler($firephp);
    }

    public function getEm()
    {
        return $this->_bootstrap->getEm();
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

    public function logDebug($message, $context)
    {
        $this->_logger->addDebug($message, (array)$context);
    }

    public function execute($actionName)
    {
        $this->setView($this->_matches['action']);
        $this->$actionName();

        return $this->render();
    }

    public function render()
    {
        return $this->_twig->render(
            $this->getTemplate(),
            $this->_context
        );
    }
}
