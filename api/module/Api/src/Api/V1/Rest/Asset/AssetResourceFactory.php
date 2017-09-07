<?php
namespace Api\V1\Rest\Asset;

use Api\V1\Resource\ResourceFactoryTrait;

class AssetResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $config = $services->get('Config');
        $awsConfig = $config['aws'];
        if ($awsConfig['useS3Storage'] === true) {
            $assetConfig = $config['assets']['s3'];
        } else {
            $assetConfig = $config['assets']['dev'];
        }

        $assetService = $services->get('Api\\V1\\Service\\AssetService');
        $eventService = $services->get('Api\\V1\\Service\\EventService');
        $assetResource = new AssetResource($assetConfig, $assetService, $eventService);
        $assetResource = $this->initialize($assetResource, $services);


        return $assetResource;
    }
}
