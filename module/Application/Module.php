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
        // List of class name of models for which we will configure \Application\Mapper\*
        // (so we can receive \Application\Model\* object when querying database)
        $models = array(
            'Answer',
            'Category',
            'Country',
            'Geoname',
            'Question',
            'Questionnaire',
            'Role',
            'Setting',
            'Survey',
            'User',
        );

        $factories = array();
        foreach ($models as $model) {
            $factories['Application\Mapper\\' . $model . 'Mapper'] = function($sm) use ($model) {
                        $tableGateway = $sm->get($model . 'TableGateway');
                        $fullMapperName = '\Application\Mapper\\' . $model . 'Mapper';
                        $mapper = new $fullMapperName($tableGateway);

                        return $mapper;
                    };

            $factories[$model . 'TableGateway'] = function($sm) use ($model) {
                        $tableName = lcfirst($model);
                        $fullModelName = '\Application\Model\\' . $model;
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new $fullModelName(array('id'), $tableName, $dbAdapter));

                        return new TableGateway($tableName, $dbAdapter, null, $resultSetPrototype);
                    };
        }

        return array(
            'factories' => $factories
        );
    }

    public function onBootstrap(EventInterface $e)
    {
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
