<?php

namespace Application;

use Locale;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface, ServiceProviderInterface, BootstrapListenerInterface
{

    private static $serviceManager;

    /**
     * Extremely dirty way to retrieve ServiceManger. Highly discouraged to use it.
     * @return \Zend\ServiceManager\ServiceManager
     */
    public static function getServiceManager()
    {
        return self::$serviceManager;
    }

    /**
     * Yet another extremely dirty way to retrieve Entity Manager. Highly discouraged to use it.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public static function getEntityManager()
    {
        return self::getServiceManager()->get('Doctrine\ORM\EntityManager');
    }

    public function getServiceConfig()
    {
        return [
        ];
    }

    public function onBootstrap(EventInterface $e)
    {
        self::$serviceManager = $e->getApplication()->getServiceManager();

        $config = self::$serviceManager->get('Configuration');
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig, null, null);
        Container::setDefaultManager($sessionManager);

        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->detectBrowserLocale($e);
        $this->deactivateLayout($e);

        // Register a strategy to control what happens when access is denied
        $t = $e->getTarget();
        $t->getEventManager()->attach(
                $t->getServiceManager()->get('ZfcRbac\View\Strategy\UnauthorizedStrategy')
        );

        self::getEntityManager()->getEventManager()->addEventSubscriber(new Service\ActivityRecorder());
    }

    /**
     * Deactivate layout for everything
     * @param \Zend\Mvc\MvcEvent $e
     */
    protected function deactivateLayout(MvcEvent $e)
    {
        $sharedEvents = $e->getApplication()->getEventManager()->getSharedManager();
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function ($e) {
            $result = $e->getResult();
            if ($result instanceof \Zend\View\Model\ViewModel) {
                $result->setTerminal(true);
            }
        });
    }

    /**
     * Detect browser locale or allow user to switch locale
     * @param \Zend\Mvc\MvcEvent $e
     */
    protected function detectBrowserLocale(MvcEvent $e)
    {
        $default = 'en';
        $supported = ['en', 'fr'];

        if ($e->getApplication()->getRequest() instanceof \Zend\Http\Request)
            $requested = $e->getApplication()->getRequest()->getQuery('lang');
        else
            $requested = null;

        // Language switch by user
        if ($requested) {
            $preference = $requested;
        }
        // Or keep session value
        elseif (isset($_COOKIE['lang'])) {
            $preference = $_COOKIE['lang'];
        }
        // If nothing else, read browser configuration
        else {
            $preference = Locale::acceptFromHttp(getenv('HTTP_ACCEPT_LANGUAGE'));
        }

        // Match preferred language to those available, defaulting to generic English
        $locale = Locale::lookup($supported, $preference, false, $default);

        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator->setLocale($locale);
        Locale::setDefault($locale);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConsoleUsage(\Zend\Console\Adapter\AdapterInterface $console)
    {
        $result = ['GIMS commands'];

        $config = $this->getConfig();
        foreach ($config['console']['router']['routes'] as $c) {
            $result[$c['options']['route']] = $c['description'];
        }

        return $result;
    }

}
