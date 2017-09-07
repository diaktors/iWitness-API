<?php
namespace Api\V1\Rpc\Asset;

use Api\V1\Service\Config\AssetConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $controller
     * @return \Api\V1\Rpc\Asset\AssetController
     */
    public function createService(ServiceLocatorInterface $controller)
    {
        $serviceLocator = $controller->getServiceLocator();

        $config = $serviceLocator->get('Config');

        $awsConfig = $config['aws'];
        if ($awsConfig['useS3Storage'] === true) {
            $assetConfig = new AssetConfig($config['assets']['s3']);
        } else {
            $assetConfig = new AssetConfig($config['assets']['dev']);
        }

        $sendFileConfig = $config['sendFile'];
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        $authentication = $serviceLocator->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $authorization = $serviceLocator->get('Api\\V1\\Security\\Authorization\\AclAuthorization');
        $assetService = $serviceLocator->get('Api\\V1\\Service\\AssetService');

        return new AssetController($sendFileConfig, $assetConfig, $assetService, $authentication, $authorization, $logger);
    }
}