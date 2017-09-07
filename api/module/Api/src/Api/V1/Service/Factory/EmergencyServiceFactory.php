<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\Config\AssetConfig;
use Api\V1\Service\Config\EventConfig;
use Api\V1\Service\EmergencyService;
use Api\V1\Service\EventService;
use Aws\S3\S3Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EmergencyServiceFactory implements FactoryInterface
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
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        /** @var \Perpii\Message\EmailManager $emailManager */
        $emailManager = $serviceLocator->get('Perpii\\Message\\EmailManager');
        /** @var \Perpii\Message\SmsManager $smsManager */
        $smsManager = $serviceLocator->get('Perpii\\Message\\SmsManager');
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        return new EmergencyService($emailManager, $smsManager, $entityManager, $logger);
    }
}