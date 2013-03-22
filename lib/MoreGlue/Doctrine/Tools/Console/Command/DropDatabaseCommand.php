<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;

class DropDatabaseCommand extends DropDatabaseDoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setHelp(<<<EOT
The <info>%command.name%</info> command drops the default connections
database:

<info>%command.full_name%</info>

The --force parameter has to be used to actually drop the database.

You can also optionally specify the name of a connection to drop the database
for:

<info>%command.full_name% --connection=default</info>

<error>Be careful: All data in a given database will be lost when executing
this command.</error>
EOT
        );
    }
}
