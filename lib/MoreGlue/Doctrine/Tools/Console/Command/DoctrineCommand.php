<?php

namespace MoreGlue\Doctrine\Tools\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Doctrine\ORM\Tools\EntityGenerator;

abstract class DoctrineCommand extends Command
{
    protected function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');

        return $entityGenerator;
    }

    protected function getEntityManager($name = null)
    {
        return $this->getHelper('em')->getEntityManager();
    }

    protected function getDoctrineConnection($name = null)
    {
        return $this->getHelper('db')->getConnection();
    }
}
