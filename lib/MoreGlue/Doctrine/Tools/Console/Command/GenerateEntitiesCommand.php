<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Doctrine\Bundle\DoctrineBundle\Command\GenerateEntitiesDoctrineCommand;

class GenerateEntitiesCommand extends GenerateEntitiesDoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generates entity classes
and method stubs from your mapping information:

You have to limit generation of entities:

* To a bundle:

  <info>%command.full_name% MyCustomBundle</info>

* To a single entity:

  <info>%command.full_name% MyCustomBundle:User</info>
  <info>%command.full_name% MyCustomBundle/Entity/User</info>

* To a namespace

  <info>%command.full_name% MyCustomBundle/Entity</info>

If the entities are not stored in a bundle, and if the classes do not exist,
the command has no way to guess where they should be generated. In this case,
you must provide the <comment>--path</comment> option:

  <info>%command.full_name% Blog/Entity --path=src/</info>

By default, the unmodified version of each entity is backed up and saved
(e.g. Product.php~). To prevent this task from creating the backup file,
pass the <comment>--no-backup</comment> option:

  <info>%command.full_name% Blog/Entity --no-backup</info>

<error>Important:</error> Even if you specified Inheritance options in your
XML or YAML Mapping files the generator cannot generate the base and
child classes for you correctly, because it doesn't know which
class is supposed to extend which. You have to adjust the entity
code manually for inheritance to work!

EOT
        );
    }

    protected function getContainer()
    {

    }
}
