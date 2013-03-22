<?php

namespace MoreGlue\Symfony\Component\Routing\Loader;

use Symfony\Component\Routing\Loader;
use Symfony\Component\Routing\Route;

class AnnotationClassLoader extends Loader\AnnotationClassLoader
{
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        $defaults = array('annotation' => $annot, 'controller' => $class->name, 'action' => $method->name);
        $route->setDefaults($defaults);
    }
}
