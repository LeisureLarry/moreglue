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
        if ($helperSetCandidate instanceof Symfony\Component\Console\Helper\HelperSet) {
            $helperSet = $helperSetCandidate;
            break;
        }
    }
}

$helperSet = ($helperSet) ?: new Symfony\Component\Console\Helper\HelperSet();

// Replacement for ConsoleRunner::run():
$cli = new Symfony\Component\Console\Application(
    'MoreGlue Command Line Interface',
    MoreGlue\Framework::VERSION
);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);

// Register framework commands
MoreGlue\Framework\Tools\Console\ConsoleRunner::addCommands($cli);

// Runs console application
$cli->run();
