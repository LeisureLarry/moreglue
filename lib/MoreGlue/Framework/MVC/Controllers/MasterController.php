<?php

namespace MoreGlue\Framework\MVC\Controllers;

use \Monolog\Handler\FirePHPHandler;
use \Monolog\Logger;
use \Symfony\Bridge\Twig\Extension\RoutingExtension;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\RedirectResponse;

class MasterController
{
    protected $_application;
    protected $_logger;
    protected $_view;
    protected $_redirect;
    protected $_context = array();

    public function __construct($application)
    {
        $this->_application = $application;
    }

    public function getApplication()
    {
        return $this->_application;
    }

    protected  function getLogger()
    {
        if (empty($this->_logger)) {
            $firephp = new FirePHPHandler();
            $this->_logger = new Logger('Monolog Logger');
            $this->_logger->pushHandler($firephp);
        }

        return $this->_logger;
    }

    public function getBootstrap()
    {
        return $this->getApplication()->getBootstrap();
    }

    public function getEm()
    {
        return $this->getBootstrap()->getEm();
    }

    public function getRequest()
    {
        return $this->getApplication()->getRequest();
    }

    public function getPost()
    {
        $post = false;
        if ($this->getRequest()->isMethod('POST')) {
            $post = $this->getRequest()->request->all();
        }

        return $post;
    }

    public function getMatches()
    {
        return $this->getApplication()->getMatches();
    }

    public function getMatch($key)
    {
        return $this->getApplication()->getMatch($key);
    }

    public function getTwig()
    {
        return $this->getApplication()->getTwig();
    }

    public function setView($action)
    {
        $view = preg_replace('/Action$/', '', $action);
        $this->_view = lcfirst($view);
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
        $namespacedControllerName = $this->getMatch('controller');
        $controller = preg_replace('/Controller$/', '', basename($namespacedControllerName));
        $bundle = preg_replace('/Bundles(.*)Controllers/', '$1', stripslashes(dirname($namespacedControllerName)));

        $template = $controller . '/' . $this->getView();
        $namespace = '@' . $bundle;

        return $namespace . '/' . $template;
    }

    public function execute()
    {
        $action = $this->getMatch('action');
        $this->setView($action);
        $this->$action();

        if (empty($this->_redirect)) {
            $response = new Response($this->render());
        } else {
            $response = new RedirectResponse($this->_redirect);
        }

        return $response;
    }

    public function logDebug($message, $context)
    {
        $this->getLogger()->addDebug($message, (array)$context);
    }

    public function redirect($name, $parameters = array(), $relative = false)
    {
        $ext = new RoutingExtension($this->getApplication()->getUrlGenerator());
        $this->_redirect = $ext->getPath($name, $parameters, $relative);
    }

    public function render()
    {
        return $this->getTwig()->render(
            $this->getTemplate(),
            $this->getContext()
        );
    }
}
