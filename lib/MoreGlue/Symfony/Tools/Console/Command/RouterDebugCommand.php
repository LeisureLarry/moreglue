<?php

namespace MoreGlue\Symfony\Tools\Console\Command;

use \Symfony\Bundle\FrameworkBundle\Command;
use \Symfony\Component\Routing\RouterInterface;

class RouterDebugCommand extends Command\RouterDebugCommand
{
    public function isEnabled()
    {
        if (!$this->getContainer()->offsetExists('router')) {
            return false;
        }
        $router = $this->getContainer()->get('router');
        if (!$router instanceof RouterInterface) {
            return false;
        }

        return true;
    }
}
