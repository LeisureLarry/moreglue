<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Output\Output;
use \Symfony\Component\Console\Command\Command;
use \Doctrine\Common\DataFixtures\Loader as FixturesLoader;
use \Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use \Doctrine\Common\DataFixtures\Purger\ORMPurger;
use \InvalidArgumentException;

class FixtureCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('doctrine:fixtures:load')
            ->setDescription('Load data fixtures to your database.')
            ->addOption('fixtures', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory or file to load data fixtures from.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
            ->addOption('purge-without-truncate', null, InputOption::VALUE_NONE, 'Purge data without using a database-level TRUNCATE statement')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command loads data fixtures from your bundles:

  <info>%command.full_name%</info>

You can also optionally specify the path to fixtures with the <info>--fixtures</info> option:

  <info>%command.full_name% --fixtures=/path/to/fixtures1 --fixtures=/path/to/fixtures2</info>

If you want to append the fixtures instead of flushing the database first you can use the <info>--append</info> option:

  <info>%command.full_name% --append</info>

By default Doctrine Data Fixtures uses a TRUNCATE statement to drop the existing rows from the database.
If you want to use DELETE statements instead you can use the <info>--purge-without-truncate</info> flag:

  <info>%command.full_name% --purge-without-truncate</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getHelper('em')->getEntityManager();

        if ($input->isInteractive() && !$input->getOption('append')) {
            $dialog = $this->getHelper('dialog');
            if (!$dialog->askConfirmation($output, '<question>Careful, database will be purged. Do you want to continue? (y/n)</question>', false)) {
                return;
            }
        }

        if ($path = $input->getOption('fixtures')) {
            if (!is_dir($path)) {
                throw new \InvalidArgumentException('The specified option "fixtures" is not a valid path.');
            }
        } elseif ($helper = $this->getHelper('fixtures')) {
            $path = $helper->getArg('path');
            if (!is_dir($path)) {
                throw new \InvalidArgumentException('The specified helper "fixtures" is not a valid path.');
            }
        }

        $loader = new FixturesLoader();
        $loader->loadFromDirectory($path);

        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $purger = new ORMPurger($em);
        $purger->setPurgeMode($input->getOption('purge-without-truncate') ? ORMPurger::PURGE_MODE_DELETE : ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
        $executor->setLogger(function($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($fixtures, $input->getOption('append'));
    }
}
