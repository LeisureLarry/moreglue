<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Doctrine\DBAL\Migrations\Tools\Console\Command;
use \MoreGlue\Doctrine\Tools\Console\Configurator\MigrationConfigurator;

class StatusCommand extends Command\StatusCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('doctrine:migrations:status');
    }
    
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        $configuration = parent::getMigrationConfiguration($input, $output);
        $configurator = new MigrationConfigurator($this, $configuration);
        return $configurator->configure();
    }
}
