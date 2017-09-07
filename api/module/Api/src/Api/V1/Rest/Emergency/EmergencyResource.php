<?php
namespace Api\V1\Rest\Emergency;

use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\EmergencyService;
use Api\V1\Service\EventService;
use Perpii\InputFilter\InputFilterTrait;
use Api\V1\Resource\ResourceAbstract;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class EmergencyResource extends ResourceAbstract
{

    use EmergencyValidatorTrait;

    /** @var EventService */
    private $eventService;

    /**
     * @return \Api\V1\Service\EmergencyService
     */
    private function getEmergencyService()
    {
        return $this->dataService;
    }

    /**
     * @return \Api\V1\Service\EventService
     */
    private function getEventService()
    {
        return $this->eventService;
    }

    /**
     * @param \Api\V1\Service\EmergencyService $emergencyService
     * @param EventService $eventService
     * @internal param \Perpii\Message\EmailManager $emailManager
     * @internal param \Perpii\Message\SmsManager $smsManager
     * @internal param \Perpii\View\ViewHelper $viewHelper
     */
    public function __construct(
        EmergencyService $emergencyService,
        EventService $eventService
    )
    {
        parent::__construct($emergencyService);
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
        try{
            $authorizationResult = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_CREATE, false);
            if ($authorizationResult !== true) {
                return $authorizationResult;
            }
            $data        = (array)$data;

            $inputFilter = $this->getCreateInputFilter();
            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

			$data = $inputFilter->getValues();
			$safe = isset($data['msgtype']) ? $data['msgtype'] : '';
			$dialno = isset($data['dialno']) ? $data['dialno'] : '';
            /** @var \Api\V1\Entity\Event $event */
            $event = $this->getEventService()->find($data['eventId']);
            if (!$event) {
                return new ApiProblem(404, 'The requested event ' . $data['eventId'] . ' is not found');
            }

            /** @var \Api\V1\Entity\User $user */
            $user = $event->getUser();
            if (!$user || strtolower($user->getId()) !== strtolower($data['userId'])) {
                return new ApiProblem(404, 'The requested user ' . $data['userId'] . ' is not found');
            }

            $this
                ->getEmergencyService()
                ->call911($event, $user, $safe, $dialno);

            return new Emergency($data['eventId']);
        }catch (\Exception $ex){
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }


    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_EMERGENCY;
    }
}
