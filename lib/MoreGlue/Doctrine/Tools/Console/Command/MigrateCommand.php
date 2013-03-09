<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Doctrine\DBAL\Migrations\Tools\Console\Command;

class MigrateCommand extends Command\MigrateCommand
{
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        $configuration = parent::getMigrationConfiguration($input, $output);
        if ($helper = $this->getHelper('migrations')) {
            $namespace = $helper->getArg('namespace');
            if (empty($namespace)) {
                throw new \InvalidArgumentException('The specified helper "migrations" has no valid namespace.');
            }
            $configuration->setMigrationsNamespace($namespace);

            $path = $helper->getArg('path');
            if (!is_dir($path)) {
                throw new \InvalidArgumentException('The specified helper "migrations" has no valid path.');
            }
            $configuration->setMigrationsDirectory($path);
            $configuration->registerMigrationsFromDirectory($path);
        }
        return $configuration;
    }
}
