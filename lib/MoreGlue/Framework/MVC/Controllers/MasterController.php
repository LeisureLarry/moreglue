<?php

namespace MoreGlue\Framework\MVC\Controllers;

use \DI\Annotations\Inject;
use \Symfony\Bridge\Twig\Extension\RoutingExtension;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \MoreGlue\Framework\MVC\Views\View;

class MasterController
{
    /**
     * @Inject("request")
     */
    protected $_request;

    /**
     * @Inject("router.matches")
     */
    protected $_matches;

    /**
     * @Inject("doctrine.em")
     */
    protected $_em;

    /**
     * @Inject("router.generator.url", lazy=true)
     */
    protected $_urlGenerator;

    /**
     * @Inject("logger", lazy=true)
     */
    protected $_logger;

    /**
     * @Inject("twig", lazy=true)
     */
    protected $_twig;

    protected $_view;
    protected $_redirect;
    protected $_data = array();

    public function getRequest()
    {
        return $this->_request;
    }

    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    public function getMatches()
    {
        return $this->_matches;
    }

    public function getMatch($key)
    {
        return $this->_matches->get($key);
    }

    public function getPost()
    {
        $post = false;

        if ($this->getRequest()->isMethod('POST')) {
            $post = $this->getRequest()->request->all();
        }

        return $post;
    }

    public function getEm()
    {
        return $this->_em;
    }

    protected  function getLogger()
    {
        return $this->_logger;
    }

    public function getTwig()
    {
        return $this->_twig;
    }

    public function getView()
    {
        return $this->_view;
    }

    public function addData($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function execute()
    {
        $matches = $this->getMatches();
        $this->_view = new View($matches);

        $action = $matches->get('action');
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
        $ext = new RoutingExtension($this->_urlGenerator);
        $this->_redirect = $ext->getPath($name, $parameters, $relative);
    }

    public function render()
    {
        $template = $this->_view->get();

        return $this->getTwig()->render(
            $template,
            $this->getData()
        );
    }
}
