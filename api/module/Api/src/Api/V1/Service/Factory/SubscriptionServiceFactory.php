<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\SubscriptionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SubscriptionServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \Exception
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $entityManager->getFilters()->enable("soft-deletable");
        $config = $serviceLocator->get('config')['paymentGateWays']['webCheckout'];
        if ($config['getWay'] == 'PayPal') {
            $paymentService = $serviceLocator->get('Api\\V1\\Service\\Payment\\PayPalService');
        } elseif ($config['getWay'] == 'Authorize.Net') {
            $paymentService = $serviceLocator->get('Api\\V1\\Service\\Payment\\AuthorizeNetService');
        } else {
            throw new \Exception('Invalid payment get way name, please change it in  config[paymentGateWays][webCheckout][getWay]');
        }

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $googleInAppService = $serviceLocator->get('Api\\V1\\Service\\Payment\\GoogleInAppService');
        $appleInAppService = $serviceLocator->get('Api\\V1\\Service\\Payment\\AppleInAppService');

        return new SubscriptionService($config,
            $paymentService,
            $googleInAppService,
            $appleInAppService,
            $entityManager,
            $logger
        );
    }
}