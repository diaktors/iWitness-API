<?php
/**
 * Created by PhpStorm.
 * User: BaoChau
 * Date: 7/10/14
 * Time: 11:58
 */

namespace Api\V1\Service\Payment;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AppleInAppServiceFactory implements FactoryInterface
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
        $config = $serviceLocator->get('config')['paymentGateWays']['appleStore'];

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        return new AppleInAppService($config, $logger, $entityManager);
    }
}