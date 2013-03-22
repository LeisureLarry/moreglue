<?php

namespace MoreGlue;

use \Webmasters\Doctrine\Bootstrap;
use \MoreGlue\Framework\Application;

class Framework
{
    const VERSION = '0.1-ALPHA1';

    protected static $_singletonInstance = null;

    protected $_connectionOptions;
    protected $_applicationOptions;
    protected $_bootstrap;
    protected $_application;

    public static function getInstance($connectionOptions = array(), $applicationOptions = array())
    {
        if (self::$_singletonInstance === null) {
            self::$_singletonInstance = new Framework($connectionOptions, $applicationOptions);
        }
        return self::$_singletonInstance;
    }

    protected function __construct($connectionOptions, $applicationOptions)
    {
        $this->_connectionOptions = $connectionOptions;
        $this->_applicationOptions = $applicationOptions;
    }

    public function getBootstrap()
    {
        if ($this->_bootstrap === null) {
            $this->_bootstrap = Bootstrap::getInstance($this->_connectionOptions, $this->_applicationOptions);
        }

        return $this->_bootstrap;
    }

    public function getEntityManager()
    {
        return $this->getBootstrap()->getEm();
    }

    public function getApplication()
    {
        if ($this->_application === null) {
            $this->_application = new Application($this);
        }

        return $this->_application;
    }

    public function dispatchApplication()
    {
        $response = $this->getApplication()->dispatch();
        $response->send();
    }
}
