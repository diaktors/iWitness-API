<?php
namespace Api;

use Perpii\Log\Listener\Request;
use Perpii\Log\Listener\Response;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Uri\UriFactory;
use ZF\Apigility\Provider\ApigilityProviderInterface;


class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        UriFactory::registerScheme('chrome-extension', 'Zend\Uri\Uri');

        $config = include __DIR__ . '/../../config/module.config.php';
        $applicationConfig = include __DIR__ . '/../../config/module.ext.config.php';
        return array_merge_recursive($config, $applicationConfig);
    }

    public function getHydratorConfig()
    {
        return include __DIR__ . '/../../config/module.hydrator.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'ZF\Apigility\Autoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $application = $mvcEvent->getApplication();
        $serviceManager = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
            function ($event) use ($serviceManager) {
                if ($event->getParam('exception')) {
                    /** @var  \Psr\Log\LoggerInterface $logger */
                    $logger = $serviceManager->get('Psr\\Log\\LoggerInterface');
                    $logger->critical($event->getParam('exception'));
                }
            }
        );

        $config = $serviceManager->get('config')['logger'];
        if ($config && isset($config['logRequestResponse']) && $config['logRequestResponse'] == true) {
            $eventManager = $application->getEventManager();
            $moduleRouteListener = new ModuleRouteListener();
            $moduleRouteListener->attach($eventManager);

            //log all request
            $eventManager->attach(
                new Request($serviceManager->get('Psr\\Log\\LoggerInterface'))
            );
            //log all response
            $eventManager->attach(
                new Response($serviceManager->get('Psr\\Log\\LoggerInterface'))
            );
        }
    }
}
