<?php

namespace Api\V1\Rpc\Contact;

use Api\V1\Hydrator\Factory\ContactHydratorFactory;
use Api\V1\Hydrator\Factory\UserHydratorFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class ContactControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        $authorization = $services->get('Api\V1\Security\Authorization\AclAuthorization');
        $authentication = $services->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $contactService = $services->get('Api\\V1\\Service\\ContactService');
        $logger = $services->get('Psr\\Log\\LoggerInterface');
        $emailManager = $services->get('Perpii\\Message\\EmailManager');

        $contactHydratorFactory= new  ContactHydratorFactory($services);
        $contactHydrator = $contactHydratorFactory->createService($services) ;

        $userHydratorFactory= new  UserHydratorFactory($services);
        $userHydrator = $userHydratorFactory->createService($services) ;

        return new ContactController($authentication, $authorization, $logger, $contactService, $emailManager, $contactHydrator, $userHydrator);
    }
} 