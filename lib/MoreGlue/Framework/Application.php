<?php

namespace MoreGlue\Framework;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\Routing;
use \DI\MetadataReader\CachedMetadataReader;
use \DI\MetadataReader\DefaultMetadataReader;

class Application
{
    protected static $_singletonInstance = null;

    protected $_kernel;
    protected $_container;

    public static function getInstance(array $connectionOptions, array $applicationOptions)
    {
        if (self::$_singletonInstance === null) {
            self::$_singletonInstance = new Application($connectionOptions, $applicationOptions);
        }
        return self::$_singletonInstance;
    }

    protected function __construct($connectionOptions, $applicationOptions)
    {
        $this->_kernel = Kernel::getInstance($connectionOptions, $applicationOptions);

        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            $applicationOptions['vendorDir'] . '/symfony/routing/Symfony/Component/Routing/Annotation/Route.php'
        );
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            $applicationOptions['vendorDir'] . '/mnapoli/php-di/src/DI/Annotations/Inject.php'
        );

        $this->_container = $this->getKernel()->getContainer();
        $applicationOptions = $this->_container->get('applicationOptions');

        $annotationReader = new CachedMetadataReader(
            new DefaultMetadataReader(),
            $this->_container->get('doctrine.cache'),
            $applicationOptions['debug_mode']
        );

        $this->_container->setMetadataReader($annotationReader);
    }

    protected function __clone()
    {
    }

    public function getKernel()
    {
        return $this->_kernel;
    }

    public function getContainer()
    {
        return $this->_container;
    }

    public function dispatch()
    {
        try {
            $response = $this->_execute();
        } catch (Routing\Exception\ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (Exception $e) {
            $response = new Response('An error occurred', 500);
        }

        return $response;
    }

    protected function _execute()
    {
        $matches = $this->getContainer()->get('router.matches');

        $namespacedControllerName = $matches->get('controller');
        $actionName = $matches->get('action');

        $controller = $this->getContainer()->get($namespacedControllerName);
        return $controller->execute($actionName);
    }
}
