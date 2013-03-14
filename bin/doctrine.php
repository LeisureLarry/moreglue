<?php

$vendorPath = realpath(__DIR__ . '/../../..');

@include_once $vendorPath . '/autoload.php';
$configFile = $vendorPath . '/../config/cli.cfg.php';

$helperSet = null;
if (file_exists($configFile)) {
    if ( ! is_readable($configFile)) {
        trigger_error(
            'Configuration file [' . $configFile . '] does not have read permission.', E_ERROR
        );
    }

    require $configFile;

    foreach ($GLOBALS as $helperSetCandidate) {
        if ($helperSetCandidate instanceof \Symfony\Component\Console\Helper\HelperSet) {
            $helperSet = $helperSetCandidate;
            break;
        }
    }
}

$helperSet = ($helperSet) ?: new \Symfony\Component\Console\Helper\HelperSet();

// Replacement for ConsoleRunner::run():
$cli = new Symfony\Component\Console\Application(
    'Doctrine Command Line Interface',
    \Doctrine\ORM\Version::VERSION
);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);

// Register all default Doctrine commands
Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands($cli);

// Add custom commands
$cli->addCommands(array(
   new MoreGlue\Doctrine\Tools\Console\Command\DiffCommand(),
   new MoreGlue\Doctrine\Tools\Console\Command\MigrateCommand(),
   new MoreGlue\Doctrine\Tools\Console\Command\StatusCommand(),
   new MoreGlue\Doctrine\Tools\Console\Command\FixtureCommand(),
   new MoreGlue\Symfony\Tools\Console\Command\PhpStormCommand()
));

// Runs console application
$cli->run();
