<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Locale;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface, ServiceProviderInterface, BootstrapListenerInterface
{

    public function getServiceConfig()
    {
        return array(
        );
    }

    public function onBootstrap(EventInterface $e)
    {
        // @TODO: This should be part of config file, but could not find a way to do it properly
        $em = $e->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'geometry');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('polygon', 'polygon');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('linestring', 'linestring');

        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->detectBrowserLocale($e);
    }

    /**
     * Detect browser locale or allow user to switch locale
     * @param \Zend\Mvc\MvcEvent $e
     */
    protected function detectBrowserLocale(MvcEvent $e)
    {
        $default = 'en';
        $supported = array('en', 'fr');

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
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleUsage(\Zend\Console\Adapter\AdapterInterface $console)
    {

        return array(
            'Application module commands',
            'database update' => "Update database schema based on data/migrations/",
        );
    }

}
