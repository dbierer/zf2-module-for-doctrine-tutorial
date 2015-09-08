<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Filter;
use Application\Repository\AttendeeRepo;
use Application\Repository\EventRepo;
use Application\Repository\RegistrationRepo;

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
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getControllerConfig()
    {
        return array(
          'initializers' => [
              'application-inject-repos' => function ($instance, $cm) {
                  if ($instance instanceof \Application\Controller\RepoAwareInterface) {
                      $sm = $cm->getServiceLocator();
                      $instance->setEventRepo($sm->get('application-repo-event'));
                      $instance->setAttendeeRepo($sm->get('application-repo-attendee'));
                      $instance->setRegistrationRepo($sm->get('application-repo-registration'));
                  }
              }
          ],
          'invokables' => [
            'Application\Controller\Index'  => 'Application\Controller\IndexController',
            'Application\Controller\Signup' => 'Application\Controller\SignupController',
            'Application\Controller\Admin'  => 'Application\Controller\AdminController',
          ],  
        );
    }
    // Add this method:
    public function getServiceConfig()
    {
        return [
            'invokables' => [
                'application-entity-event'        => 'Application\Entity\Event',
                'application-entity-registration' => 'Application\Entity\Registration',
                'application-entity-attendee'     => 'Application\Entity\Attendee',
            ],
            'factories' => [
                'application-repo-event'       => function ($sm) {
                    $em = $sm->get('doctrine.entitymanager.orm_default');
                    return new EventRepo($em, $em->getClassMetadata('Application\Entity\Event'));
                },
                'application-repo-registration'=> function ($sm) {
                    $em = $sm->get('doctrine.entitymanager.orm_default');
                    return new RegistrationRepo($em, $em->getClassMetadata('Application\Entity\Registration'));
                },
                'application-repo-attendee'    => function ($sm) {
                    $em = $sm->get('doctrine.entitymanager.orm_default');
                    return new AttendeeRepo($em, $em->getClassMetadata('Application\Entity\Attendee'));
                },
                'application-data-filter'   => function ($sm) {
                    $filter = new Filter\FilterChain();
                    $filter->attach(new Filter\StringTrim())
                          ->attach(new Filter\StripTags());
                    return $filter;
                },
            ],
        ];
    }


}
