<?php
namespace Api\V1\Rest\Asset;

use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\EventService;
use Api\V1\Service\AssetService;
use Exception;
use Zend\File\Transfer\Adapter\Http;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use Api\V1\Resource\ResourceAbstract;
use Zend\View\Model\JsonModel;

class AssetResource extends ResourceAbstract
{
    use AssetValidatorTrait;

    /** @var */
    private $config;

    /** @var \Api\V1\Service\EventService|null */
    private $eventService = null;

    public function __construct(array $config, AssetService $assetService, EventService $eventService)
    {
        parent::__construct($assetService);

        $this->config = $config;
        $this->eventService = $eventService;
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
	{
	   // $log_file_path =  "/volumes/log/api/test-log.log";
		//$ha = fopen($log_file_path, 'a+') or die('Cannot open file:  '.$log_file_path);

        try {
            $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_CREATE);
            if ($result !== true) {
                return $result;
            }

            if (!isset($this->config['baseDir'])) {
                return new ApiProblemModel(new ApiProblem(417, 'Missing path for asset base folder in configuration'));
            }

            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $this->getPost()->toArray(),
                $this->getFiles()->toArray()
            );		



            $this->validateMediaUploaded($data);
           // fwrite($ha, "Uploading start :  ");
            $user = $this->getIdentity();
            $event = $this->eventService->createEventIfIdNull($data['event-id'], $data['lat'], $data['lng'], $user);



		if($data['media']['size'] == 0)
		{
			$error = ["status"=>202, "message"=>'Invalid Video'];
			echo json_encode($error);
			exit;
		}


            $asset = $this->getAssetService()->upload($data, $event, $user);
            return $asset;
        } catch (Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_ASSET;
    }

    /**
     * @return \Api\V1\Service\AssetService
     */
    private function getAssetService()
    {
        return $this->dataService;
    }
}
