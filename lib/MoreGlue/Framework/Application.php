<?php

namespace MoreGlue\Framework;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\Routing;

class Application
{
    protected $_framework;
    protected $_request;
    protected $_context;
    protected $_matcher;
    protected $_matches;

    public function __construct($framework)
    {
        $this->_framework = $framework;
        $this->_request = Request::createFromGlobals();

        if (!$this->_request->hasPreviousSession()) {
            $session = new Session();
            $session->start();
            $this->_request->setSession($session);
        }
    }

    public function getFramework()
    {
        return $this->_framework;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getBootstrap()
    {
        return $this->getFramework()->getBootstrap();
    }

    public function getContext()
    {
        if (empty($this->_context)) {
            $context = new Routing\RequestContext();
            $context->fromRequest($this->_request);
            $this->_context = $context;
        }

        return $this->_context;
    }

    public function getMatcher()
    {
        if (empty($this->_matcher)) {
            $vendorPath = realpath(__DIR__ . '/../../../../..');
            $srcPath = realpath($vendorPath . '/../src');

            $annotationReader = $this->getBootstrap()->getAnnotationReader();
            \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
                $vendorPath . '/symfony/routing/Symfony/Component/Routing/Annotation/Route.php'
            );

            $paths = array(
                $srcPath . '/Bundles/Frontend/Controllers',
                $srcPath . '/Bundles/Backend/Controllers'
            );

            $this->_matcher = new \MoreGlue\Symfony\Component\Routing\Router(
                $annotationReader,
                $paths,
                array(),
                $this->getContext()
            );
        }

        return $this->_matcher;
    }

    public function getRoutes()
    {
        return $this->_matcher->getRouteCollection();
    }

    public function getMatches()
    {
        if (empty($this->_matches)) {
            $this->_matches = $this->getMatcher()->match($this->getRequest()->getPathInfo());
        }

        return $this->_matches;
    }

    public function getMatch($key)
    {
        $matches = $this->getMatches();
        return isset($matches[$key]) ? $matches[$key] : null;
    }

    public function getUrlGenerator()
    {
        return new Routing\Generator\UrlGenerator($this->getRoutes(), $this->getContext());
    }

    public function getTwig()
    {
        $srcPath = $this->getBootstrap()->getOption('src_path');
        $libPath = __DIR__ . '/MVC/Views';

        $isDebug = $this->getBootstrap()->isDebug();

        $loader = new \Twig_Loader_Filesystem(
            array($libPath)
        );

        $src = $this->getBootstrap()->getOption('src');
        foreach ($src as $bundle) {
            $loader->addPath(
                $srcPath . '/Bundles/' . $bundle . '/Views',
                $bundle
            );
        }

        $twig = new \Twig_Environment($loader, array('debug' => $isDebug));

        $twig->addExtension(
            new \MoreGlue\Twig\Extension\RequestExtension($this->getRequest())
        );

        $generator = $this->getUrlGenerator();
        $twig->addExtension(
            new \Symfony\Bridge\Twig\Extension\RoutingExtension($generator)
        );

        if ($isDebug) {
            $twig->addExtension(
                new \Twig_Extension_Debug()
            );
        }

        return $twig;
    }

    public function execute()
    {
        $namespacedControllerName = $this->getMatch('controller');
        $actionName = $this->getMatch('action');

        $controller = new $namespacedControllerName($this);
        return $controller->execute($actionName);
    }

    public function dispatch()
    {
        try {
            $response = $this->execute();
        } catch (Routing\Exception\ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (Exception $e) {
            $response = new Response('An error occurred', 500);
        }

        return $response;
    }
}
