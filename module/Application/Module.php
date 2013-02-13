<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface, ServiceProviderInterface
{

    public function getServiceConfig()
    {
        // List of class name of models for which we will configure ModelTable 
        // (so we can receive \Application\Model\* object when querying database)
        $models = array(
            'Answer',
            'Category',
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

    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
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
