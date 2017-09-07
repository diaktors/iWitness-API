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

class AssetCommandFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $assetService = $serviceLocator->get('Api\V1\Service\AssetService');
        $logger = $serviceLocator->get('Psr\Log\LoggerInterface');

        return new AssetCommand(null, $assetService, $logger);
    }


} 