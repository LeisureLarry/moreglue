<?php

namespace MoreGlue\Twig\Extension;

class RequestExtension extends \Twig_Extension
{
    protected $_request;

    public function __construct($request)
    {
        $this->_request = $request;
    }

    public function getFunctions()
    {
        return array(
            'request' => new \Twig_Function_Method($this, 'getRequest'),
            'session' => new \Twig_Function_Method($this, 'getSession'),
            'is_user' => new \Twig_Function_Method($this, 'isUser'),
        );
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    public function isUser($id = null)
    {
        $isUser = false;

        $session = $this->getSession();
        if ($session && $session->has('login')) {
            if ($id === null || $id == $session->get('login')) {
                $isUser = true;
            }
        }

        return $isUser;
    }

    public function getName()
    {
        return 'request';
    }
}
