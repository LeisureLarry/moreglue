<?php

namespace MoreGlue\Framework\MVC\Views;

class View
{
    protected $_view = array(0 => null, 1 => null, 2 => null);

    public function __construct($matches)
    {
        $namespacedControllerName = $matches->get('controller');

        $bundle = preg_replace('/Bundles(.*)Controllers/', '$1', stripslashes(dirname($namespacedControllerName)));
        $namespace = '@' . $bundle;

        $controller = preg_replace('/Controller$/', '', basename($namespacedControllerName));

        $action = $matches->get('action');
        $template = lcfirst(preg_replace('/Action$/', '', $action)) . '.html.twig';

        $this->setNamespace($namespace);
        $this->setController($controller);
        $this->setTemplate($template);
    }

    public function setNamespace($namespace)
    {
        $this->_view[0] = $namespace;
    }

    public function setController($controller)
    {
        $this->_view[1] = $controller;
    }

    public function setTemplate($template)
    {
        $this->_view[2] = $template;
    }

    public function get()
    {
        return implode('/', $this->_view);
    }
}
