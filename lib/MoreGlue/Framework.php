<?php

namespace MoreGlue;

use \FuelPHP\Alias\Manager as AliasManager;
use \Webmasters\Doctrine\Bootstrap;
use \MoreGlue\Framework\Application;

class Framework
{
    const VERSION = '0.1-ALPHA4';

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

        // Create a new alias manager
        $manager = new AliasManager();

        // Register the manager as prepended autoloader
        $manager->register();

        // Alias some classes
        $manager->alias(array(
             'Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand' => 'MoreGlue\Doctrine\Tools\Console\Command\DoctrineCommand',
             'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand' => 'MoreGlue\Symfony\Tools\Console\Command\ContainerAwareCommand'
        ));
    }

    protected function __clone()
    {
    }

    public function getApplication()
    {
        if ($this->_application === null) {
            $this->_application = Application::getInstance($this->_connectionOptions, $this->_applicationOptions);
        }

        return $this->_application;
    }

    public function getEntityManager()
    {
        return $this->getApplication()->getKernel()->getContainer()->get('doctrine.em');
    }

    public function dispatchApplication()
    {
        $response = $this->getApplication()->dispatch();
        $response->send();
    }
}
