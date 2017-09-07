<?php
namespace Api\V1\Rest\Device;

use Api\V1\Resource\ResourceAbstract;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\DeviceService;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class DeviceResource extends ResourceAbstract
{
    /** @var  $config array */
    private $config;

    /**
     * @param array $config
     * @param DeviceService $deviceService
     */
    public function __construct(array $config, DeviceService $deviceService)
    {
        parent::__construct($deviceService);
        $this->config = $config;
    }

    /**
     * Create device resource
     *
     * @override create($data, $uuid)
     * @param  mixed $data
     * @return ApiProblem|Device
     */
    public function create($data)
    {
        try {
            $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_CREATE);
            if ($result !== true) {
                return $result;
            }

            $data = (array)$data;
            $service = $this->getDeviceService();
            $currentUser = $this->getIdentity();
            return $service->insertUserDevice($data, $currentUser);
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return DeviceService
     */
    private function getDeviceService()
    {
        return $this->dataService;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_DEVICE;
    }
}