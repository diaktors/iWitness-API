<?php

namespace Api\V1\Service\Payment;

use Api\V1\Service\Payment\AuthorizeNetService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class PaypalServiceFactory implements FactoryInterface
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

        if (!function_exists('boolval')) { // hack for old php version
            $isSandbox = (bool)$config['sandbox'];
        } else {
            $isSandbox = boolval($config['sandbox']);
        }

        $config['PayPal']['mode'] = $isSandbox ? 'sandbox' : 'live';
        $config['PayPal']['description'] = $config['description'];

        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        return new PaypalService($config['PayPal'], $logger);
    }
}