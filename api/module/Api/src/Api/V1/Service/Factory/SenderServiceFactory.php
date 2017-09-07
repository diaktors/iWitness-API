<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 7/6/14
 * Time: 10:48 PM
 */

namespace Api\V1\Service\Factory;


use Api\V1\Service\SenderService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SenderServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $entityManager->getFilters()->enable("soft-deletable");
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        return new SenderService($entityManager, $logger);
    }
}