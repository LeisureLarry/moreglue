<?php

namespace MoreGlue\Doctrine\Tools\Console\Helper;

use \Symfony\Component\Console\Helper\Helper;

class PathHelper extends Helper
{
    protected $_name;
    protected $_path;
    protected $_namespace;

    public function __construct($name, $path, $namespace = null)
    {
        $this->_name = $name;
        $this->_path = $path;
        $this->_namespace = !empty($namespace) ? $namespace : ucfirst($name);
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getNamespace()
    {
        return $this->_namespace;
    }

    public function getName()
    {
        return $this->_name;
    }
}
