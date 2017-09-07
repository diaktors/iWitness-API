<?php
namespace Api\V1\Rpc\Asset;

use Api\V1\Controller\BaseActionController;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Api\V1\Service\Config\AssetConfig;
use Api\V1\Service\AssetService;
use Api\V1\Service\MediaFileInfo;
use Exception;
use Psr\Log\LoggerInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class AssetController extends BaseActionController
{
    /** @var array */
    private $sendFileConfig = null;
    /** @var array|AssetConfig */
    private $assetConfig = null;
    /** @var AssetService $assetService */
    private $assetService = null;

    /**
     * @param array $sendFileConfig
     * @param AssetConfig $assetConfig
     * @param AssetService $assetService
     * @param AuthenticationServiceInterface $authentication
     * @param AuthorizationInterface $authorization
     * @param LoggerInterface $logger
     * @internal param \Api\V1\Service\UserService $userService
     */
    public function __construct(
        array $sendFileConfig,
        AssetConfig $assetConfig,
        AssetService $assetService,
        AuthenticationServiceInterface $authentication,
        AuthorizationInterface $authorization,
        LoggerInterface $logger)
    {
        parent::__construct($authentication, $authorization, $logger);

        $this->sendFileConfig = $sendFileConfig;
        $this->assetConfig = $assetConfig;
        $this->assetService = $assetService;
    }

    /**
     * Point-out video url of this asset
     *
     * @return boolean
     */
    public function videoUrlAction()
    {
        try {
            return $this->mediaRendering(MediaFileInfo::TYPE_MP4, 'video/mp4');
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Media processing for rendering to client
     *
     * @param $mediaType
     * @param $mediaContentType
     * @return null|\Zend\Http\Response
     */
    private function mediaRendering($mediaType, $mediaContentType)
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $id = $router->getParam('asset_id');
            if (empty($id)) {
                throw new Exception('Provide AssetId for this action');
            }

            $asset = $this->assetService->fetch($id);
            /** @var \Api\V1\Service\MediaFileInfo $mediaInfo */
            $mediafilePath = $this->assetConfig->getAssetLocalCachePath($asset, $mediaType, false);

            // render file to client
            return $this->sendFile($mediafilePath, $mediaContentType, $this->sendFileConfig);

        } catch (Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @throws \Exception
     * @return string
     */
    function getResourceId()
    {
        return AclAuthorization::RESOURCE_ASSET;
    }
}