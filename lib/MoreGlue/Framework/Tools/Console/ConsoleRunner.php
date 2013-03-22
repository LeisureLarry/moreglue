<?php

namespace MoreGlue\Framework\Tools\Console;

use \Symfony\Component\Console\Application;
use \Doctrine\ORM\Tools\Console as Doctrine;

class ConsoleRunner extends Doctrine\ConsoleRunner
{
    static public function addCommands(Application $cli)
    {
        $cli->addCommands(array(
            // Default Doctrine Commands
            new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
            new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
            new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
            new \Doctrine\ORM\Tools\Console\Command\InfoCommand(),

            // MoreGlue Doctrine Commands
            new \MoreGlue\Doctrine\Tools\Console\Command\CreateDatabaseCommand(),
            new \MoreGlue\Doctrine\Tools\Console\Command\DropDatabaseCommand(),
            //new \MoreGlue\Doctrine\Tools\Console\Command\GenerateEntitiesCommand(),
            new \MoreGlue\Doctrine\Tools\Console\Command\DiffCommand(),
            new \MoreGlue\Doctrine\Tools\Console\Command\StatusCommand(),
            new \MoreGlue\Doctrine\Tools\Console\Command\MigrateCommand(),
            new \MoreGlue\Doctrine\Tools\Console\Command\FixtureCommand(),

            // MoreGlue Symfony Commands
            //new \MoreGlue\Symfony\Tools\Console\Command\RouterDebugCommand(),

            // MoreGlue Framework Commands
            new \MoreGlue\Framework\Tools\Console\Command\PhpStormCommand()
        ));
    }
}
