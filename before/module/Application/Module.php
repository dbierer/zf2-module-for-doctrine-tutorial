<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Model\AttendeeTable;
use Application\Model\EventTable;
use Application\Model\RegistrationTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Filter;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
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

    // Add this method:
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'EventTable' =>  function($sm) {
                    return new EventTable(EventTable::TABLE_NAME,
                                            $sm->get('Zend\Db\Adapter\Adapter'));
                },
                'RegistrationTable' =>  function($sm) {
                    return new RegistrationTable(RegistrationTable::TABLE_NAME,
                                                   $sm->get('Zend\Db\Adapter\Adapter'));
                },
                'AttendeeTable' =>  function($sm) {
                    return new AttendeeTable(AttendeeTable::TABLE_NAME, 
                                               $sm->get('Zend\Db\Adapter\Adapter'));
                },
                'RegDataFilter' => function ($sm) {
                    $filter = new Filter\FilterChain();
                    $filter->attach(new Filter\StringTrim())
                           ->attach(new Filter\StripTags());
                    return $filter;
                },
            ),
        );
    }


}
