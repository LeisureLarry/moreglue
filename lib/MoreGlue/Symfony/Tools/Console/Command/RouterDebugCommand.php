<?php

namespace MoreGlue\Symfony\Tools\Console\Command;

use \Symfony\Bundle\FrameworkBundle\Command;

class RouterDebugCommand extends Command\RouterDebugCommand
{
    public function isEnabled()
    {
        var_dump($this->getContainer());
        if (!$this->getContainer()->offsetExists('router')) {
            return false;
        }
        $router = $this->getContainer()->get('router'); var_dump($router);
        if (!$router instanceof RouterInterface) {
            return false;
        }

        return true;
    }
}
