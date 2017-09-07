<?php
/**
 * Created by PhpStorm.
 * User: corybohon
 * Date: 5/26/14
 * Time: 6:54 PM
 */

namespace Api\V1\Service\Command;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventCommandFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $eventService = $serviceLocator->get('Api\V1\Service\EventService');
        $logger = $serviceLocator->get('Psr\Log\LoggerInterface');
        return new EventCommand(null, $eventService, $logger);
    }

} 