<?php
namespace Api\V1\Rest\Event;

use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\EventService;
use Api\V1\Resource\ResourceAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Webonyx\Util\UUID;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class EventResource extends ResourceAbstract
{
    use EventValidatorTrait;

    public function __construct(EventService $eventService)
    {
        parent::__construct($eventService);
    }

    /**
     * Create a resource
     *
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
            $inputFilter = $this->getInputFilters();

            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            // starting to create event
            return $this->getEventService()->createEvent($inputFilter->getValues(), $this->getIdentity());
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        try {
            /** @var \Api\V1\Entity\Event */
            $event = $this->getEventService()->getEvent($id);

            //turn it off for calling 911 alert watching events, if we protect the video, user's trusted contacts cannot see it
            //but if we don't check security, anonymous user can watch video if they know event id (a security hole)
            //may think of protect this video by timing token or something like that
            //$result = $this->isAuthorized($event, AclAuthorization::PERMISSION_VIEW);
            //if ($result !== true) {
            //    return $result;
            //}
            return $event;
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
            $userId = $routeMatch->getParam('user_id', null);

            $queryParams = $this->getQueryParams();

            if (empty($userId) && isset($queryParams['query']['user_id'])) {
                $userId = $queryParams['query']['user_id'];
            }

            if ($userId) {
                unset($queryParams['query']['user_id']);
                $user = $this->getEventService()->findUser($userId);
                $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_VIEW);

                //not login user (Guest)
                if ($result !== true) {
                    return $result;
                }

                return $this->getEventService()->fetchAll(
                    $queryParams,
                    null,
                   function (QueryBuilder &$queryBuilder) use ($userId) {
                        $queryBuilder->innerJoin('row.user', 'u');
                        $uniqId = uniqid('id');
                        $queryBuilder->andWhere($queryBuilder->expr()->eq('u.id', ":$uniqId"));
                        $queryBuilder->andWhere($queryBuilder->expr()->eq('row.processed', ":processed"));
                        $queryBuilder->setParameter($uniqId, UUID::toBinary($userId));
                        $queryBuilder->setParameter('processed', 1);
                        $queryBuilder->andWhere('row.deleted IS NULL ');
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
                return $this->getEventService()->fetchAll(
                    $queryParams,
                    null,
                    function (QueryBuilder &$queryBuilder) {
                        $queryBuilder->andWhere($queryBuilder->expr()->eq('row.processed', ":processed"));
                        $queryBuilder->setParameter('processed', 1);
                        $queryBuilder->andWhere('row.deleted IS NULL ');
                    },
                    $this->getCollectionClass()
				);
            }
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
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
        try {
            /** @var \Api\V1\Entity\Event */
            $event = $this->getEventService()->find($id);
            if (!$event) {
                return new ApiProblem(404, 'Event with id ' . $id . ' was not found');
            }

            $result = $this->isAuthorized($event, AclAuthorization::PERMISSION_UPDATE);
            if ($result !== true) {
                return $result;
            }

            $data = (array)$data;
            $inputFilter = $this->getInputFilters($data, $event, false);
            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            return $this->getEventService()->update($event, $this->getInputFilteredValues($inputFilter, $data));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_EVENT;
    }

    /**
     * @return \Api\V1\Service\EventService
     */
    private function getEventService()
    {
        return $this->dataService;
    }
}
