<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;

class CreateDatabaseCommand extends CreateDatabaseDoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setHelp(<<<EOT
The <info>%command.name%</info> command creates the default
connections database:

<info>%command.full_name%</info>

You can also optionally specify the name of a connection to create the
database for:

<info>%command.full_name% --connection=default</info>
EOT
        );
    }
}
