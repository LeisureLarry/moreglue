<?php

namespace Moreglue\Doctrine\Tools\Console\Configurator;

class MigrationConfigurator
{
    protected $_command;
    protected $_configuration;

    public function __construct($command, $configuration)
    {
        $this->_command = $command;
        $this->_configuration = $configuration;
    }

    public function getCommand()
    {
        return $this->_command;
    }

    public function getHelper($name)
    {
        return $this->getCommand()->getHelper($name);
    }

    public function getConfiguration()
    {
        return $this->_configuration;
    }

    public function configure()
    {
        $configuration = $this->getConfiguration();
        if ($helper = $this->getHelper('migrations')) {
            $namespace = $helper->getNamespace();
            if (empty($namespace)) {
                throw new \InvalidArgumentException('The specified helper "migrations" has no valid namespace.');
            }
            $configuration->setMigrationsNamespace($namespace);

            $path = $helper->getPath();
            if (!is_dir($path)) {
                throw new \InvalidArgumentException('The specified helper "migrations" has no valid path.');
            }
            $configuration->setMigrationsDirectory($path);
            $configuration->registerMigrationsFromDirectory($path);
        }
        return $configuration;
    }
}