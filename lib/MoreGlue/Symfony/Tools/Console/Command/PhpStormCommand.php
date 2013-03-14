<?php

namespace MoreGlue\Symfony\Tools\Console\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Output\Output;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;

/*
 * A fork of the SymfonyBundle marphi/PhpStormSupportBundle adapted for MoreGlue
 * https://github.com/marphi/PhpStormSupportBundle
 */
class PhpStormCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('phpstorm:command:config')
            ->setDescription('Generate config for PhpStorm Command Tool.')
            ->addOption('alias', 'a', InputOption::VALUE_OPTIONAL, 'The alias of command', 'm')
            ->addOption('name', '', InputOption::VALUE_OPTIONAL, 'The name of command', 'MoreGlue')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generate configuration for Command Line Tool Support

Generate and save file to default path <project>/.idea/commandlinetools/MoreGlue.xml where `MoreGlue` is name of command.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands = $this->getApplication()->all();
        $alias = $input->getOption('alias');
        $name = $input->getOption('name');

        $raw = $this->commandsAsXml($name, $alias, $commands);

        $output->writeln($raw, OutputInterface::OUTPUT_RAW);
    }


    private function commandsAsXml($name, $alias, $commands)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $dom->appendChild($frameworkNode = $dom->createElement('framework'));

        $frameworkNode->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $frameworkNode->setAttribute('xsi:noNamespaceSchemaLocation', 'schemas/frameworkDescriptionVersion1.1.3.xsd');
        $frameworkNode->setAttribute('name', $name);
        $frameworkNode->setAttribute('invoke', '"$PhpExecutable$" vendor/interiete/moreglue/bin/console');
        $frameworkNode->setAttribute('alias', $alias);
        $frameworkNode->setAttribute('enabled', "true");
        $frameworkNode->setAttribute('version', 2);

        foreach ($commands as $command) {
            $frameworkNode->appendChild($commandXml = $dom->createElement('command'));

            $commandXml->appendChild($dom->createElement('name', $command->getName()));
            $commandXml->appendChild($help = $dom->createElement('help'));
            $help->appendChild($dom->createCDATASection($command->getProcessedHelp()));

            $paramElements = $this->getCommandParams($command);
            if (!empty($paramElements)) {
                $commandXml->appendChild($dom->createElement('params', $paramElements));
            }

            $optionElements = $this->getCommandOptions($command);
            if (!empty($optionElements)) {
                $commandXml->appendChild($optionsBefore = $dom->createElement('optionsBefore'));
                foreach ($optionElements as $element) {
                    $optionsBefore->appendChild($option = $dom->createElement('option'));
                    $option->setAttribute('name', $element['name']);
                    $option->setAttribute('shortcut', $element['shortcut']);
                    $option->appendChild($optionHelp = $dom->createElement('help'));
                    $optionHelp->appendChild($dom->createCDATASection($element['help']));
                }
            }
        }
        
        return $dom->saveXml();
    }

    private function getCommandParams(Command $command)
    {
        $definition = $command->getDefinition();
        $elements = array();

        foreach ($definition->getArguments() as $argument) {
            $elements[] = sprintf(
                $argument->isRequired() ? '%s' : '%s[=%s]',
                $argument->getName(),
                $argument->getDefault() ? '"' . $argument->getDefault() . '"' : 'null'
            );
        }

        foreach ($definition->getOptions() as $option) {
            $elements[] = sprintf(
                '%s--%s[=%s]',
                $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '',
                $option->getName(),
                $option->getDefault() ? '"' . $option->getDefault() . '"' : 'null'
            );
        }

        return implode(' ', $elements);
    }


    private function getCommandOptions(Command $command)
    {
        $definition = $command->getDefinition();
        $elements = array();

        foreach ($definition->getOptions() as $option) {
            $elements[] = array(
                'name' => '--' . $option->getName(),
                'shortcut' => $option->getShortcut() ? '-' . $option->getShortcut() : '',
                'help' => $option->getDescription()
            );
        }

        return $elements;
    }
}
