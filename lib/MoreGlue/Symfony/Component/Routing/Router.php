<?php

namespace MoreGlue\Symfony\Component\Routing;

use \Symfony\Component\Routing\Router as BaseRouter;
use \Symfony\Component\Routing\RequestContext;
use \Symfony\Component\Routing\RouteCollection;

class Router extends BaseRouter
{
    protected $annotationReader;
    protected $paths;

    public function __construct($annotationReader, $paths, array $options = array(), RequestContext $context = null)
    {
        $this->annotationReader = $annotationReader;
        $this->paths = $paths;
        $this->context = null === $context ? new RequestContext() : $context;
        $this->setOptions($options);
    }

    public function getRouteCollection()
    {
        if (null === $this->collection) {
            $classLoader = new \MoreGlue\Symfony\Component\Routing\Loader\AnnotationClassLoader($this->annotationReader);
            $locator = new \Symfony\Component\Config\FileLocator();
            $loader = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($locator, $classLoader);

            $collection = new RouteCollection();
            foreach ($this->paths as $path) {
                $collection->addCollection($loader->load($path));
            }
            $this->collection = $collection;
        }

        return $this->collection;
    }
}
