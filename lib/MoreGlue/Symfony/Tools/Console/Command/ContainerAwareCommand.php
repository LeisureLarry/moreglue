<?php

namespace MoreGlue\Symfony\Tools\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class ContainerAwareCommand extends Command implements ContainerAwareInterface
{
    protected $container;

    protected function getContainer()
    {
        if (null === $this->container) {
            $this->container = \DI\Container::getInstance();
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
