<?php

namespace Api\V1\Service\Payment;

use Api\V1\Service\Payment\AuthorizeNetService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class AuthorizeNetServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $config = $serviceLocator->get('config')['paymentGateWays']['webCheckout'];
        $config['Authorize.Net']['sandbox'] = $config['sandbox'];
        $config['Authorize.Net']['description'] = $config['description'];
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        return new AuthorizeNetService($config['Authorize.Net'], $logger);
    }
}