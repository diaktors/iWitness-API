<?php

namespace Api\V1\Rpc\User;

use Aws\S3\S3Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        $authorization = $services->get('Api\V1\Security\Authorization\AclAuthorization');
        $authentication = $services->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $photoService = $services->get('Api\V1\Service\PhotoService');
        $userService = $services->get('Api\\V1\\Service\\UserService');
        $logger = $services->get('Psr\\Log\\LoggerInterface');
        $emailManager = $services->get('Perpii\\Message\\EmailManager');

        //TODO: remove this
        $config      = $services->get('config');
        $awsConfig = $config['aws'];

        if ($awsConfig['useS3Storage'] === true) {
            $s3 = S3Client::factory([
                'key'    => $awsConfig['key'],
                'secret' => $awsConfig['secret']
            ]);

            $s3->registerStreamWrapper();
        }

        return new UserController($authentication, $authorization, $logger, $photoService, $userService, $emailManager);
    }
}
