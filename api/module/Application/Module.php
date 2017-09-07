<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->initializePhpSetting($mvcEvent); //change default php setting
        $this->initializeLogger($mvcEvent);
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

    /**
     * @param MvcEvent $mvcEvent
     */
    public function initializePhpSetting(MvcEvent $mvcEvent)
    {
        $application = $mvcEvent->getApplication();
        $serviceManager = $application->getServiceManager();

        $config = $serviceManager->get('config');

        if ($config && isset($config['phpSetting'])) {
            $phpSettings = (array)$config['phpSetting'];
            foreach ($phpSettings as $key => $value) {
                @ini_set($key, $value);
            }
        }
    }

    /**
     * @param MvcEvent $mvcEvent
     */
    public function initializeLogger(MvcEvent $mvcEvent)
    {
        $application = $mvcEvent->getApplication();
        $serviceManager = $application->getServiceManager();
        $logger = $serviceManager->get('logger-main');
        $serviceManager->setService('Psr\\Log\\LoggerInterface', $logger);
    }
}
