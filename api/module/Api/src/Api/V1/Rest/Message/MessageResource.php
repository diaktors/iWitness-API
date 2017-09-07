<?php
namespace Api\V1\Rest\Message;

use Api\V1\Resource\ResourceAbstract;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\DeviceService;
use Api\V1\Service\MessageService;
use Api\V1\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use Webonyx\Util\UUID;
use ZF\ApiProblem\ApiProblem;

class MessageResource extends ResourceAbstract
{
    /** @var  $config array */
    private $config;

    /**
     * @var \Api\V1\Service\UserService
     */
    private $userService;

    /** @var DeviceService $deviceService */
    private $deviceService = null;


    public function __construct(
        array $config,
        MessageService $messageService,
        UserService $userService,
        DeviceService $deviceService
    )
    {
        parent::__construct($messageService);
        $this->config = $config;
        $this->userService = $userService;
        $this->deviceService = $deviceService;
    }

    /**
     * Create message resource
     *
     * @override create($data, $uuid)
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        try {
            $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_CREATE);
            if ($result !== true) {
                return $result;
            }
            $data = (array)$data;

            if (empty($data['userIds'])) {
                throw new \Exception('UserIds  cannot be empty');
            }

            if (empty($data['message'])) {
                throw new \Exception('Message cannot be empty');
            }

            $userIds = (array)$data['userIds'];
            $message = $data['message'];

            $messageService = $this->getMessageService();

            $messageEntity = null;
            foreach ($userIds as $userId) {
                $user = $this->userService->find($userId);
                if (!$user) {
                    continue;
                }
                $messageEntity = $messageService->insertUserMessage($message, $user);
                $this->userService->pushNotification($user, $message);
            }

            return $messageEntity;

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        try {
            $event = $this->getEvent();
            $routeMatch = $event->getRouteMatch();
            $userId = $routeMatch->getParam('userId', null);
            $queryParams = $this->getQueryParams();
            if (empty($userId) && isset($queryParams['query']['userId'])) {
                $userId = $queryParams['query']['userId'];
            }

            if ($userId) {
                $user = $this->userService->getUserById($userId);
                $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_VIEW);

                //not login user (Guest)
                if ($result !== true) {
                    return $result;
                }

                return $this->getMessageService()->fetchAll(
                    $queryParams,
                    null,
                    function (QueryBuilder &$queryBuilder) use ($userId) {
                        $queryBuilder->innerJoin('row.userMessages', 'um');
                        $queryBuilder->innerJoin('um.user', 'u');
                        $uniqId = uniqid('id');
                        $queryBuilder->andWhere($queryBuilder->expr()->eq('u.id', ":$uniqId"));
                        $queryBuilder->setParameter($uniqId, UUID::toBinary($userId));
                    },
                    $this->getCollectionClass()
                );

            } else {
                $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_LIST_ALL);
                $user = $this->getIdentity();

                //not login user (Guest)
                if ($result !== true && !$user) {
                    return $result;
                }

                return parent::fetchAll();
            }
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @return MessageService
     */
    private function getMessageService()
    {
        return $this->dataService;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_MESSAGE;
    }
}