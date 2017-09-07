<?php

namespace Api\V1\Resource;

use Api\V1\Controller\ValidationException;
use Api\V1\Security\Authentication\AuthenticationTrait;
use Api\V1\Security\Authorization\AuthorizationTrait;
use Api\V1\Service\ServiceAbstract;
use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Stdlib\Hydrator;
use Perpii\Collection\Query;
use Perpii\Exception\DoctrineModuleNotFoundException;
use Perpii\Exception\EntityNotFoundException;
use Perpii\InputFilter\InputFilterTrait;
use Perpii\Log\LoggerTrait;
use Perpii\Util\String;
use Psr\Log\LoggerAwareInterface;
use Zend\Permissions\Acl\Resource;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Stdlib\RequestInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\ResourceEvent;
use Api\V1\Security\Authorization\AclAuthorization;

/**
 * Class DoctrineResource
 *
 * @package Perpii\Resource
 */
abstract class ResourceAbstract extends AbstractResourceListener implements LoggerAwareInterface, ResourceInterface
{
    use LoggerTrait;
    use AuthorizationTrait;

    /** @var  ServiceAbstract */
    protected $dataService = null;

    public function __construct(ServiceAbstract $dataService = null)
    {
        $this->dataService = $dataService;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        try {
            $result = $this->getEntityAndCheckPermission($id, AclAuthorization::PERMISSION_DELETE);
            if ($result instanceof ApiProblem) {
                return $result;
            }
            //do delete
            return $this->dataService->delete($result);
        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
        }
    }

    /**
     * Fetch a resource
     *
     * If the extractCollections array contains a collection for this resource
     * expand that collection instead of returning a link to the collection
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        try {
            $result = $this->getEntityAndCheckPermission($id, AclAuthorization::PERMISSION_VIEW);
            return $result;
        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
        }
    }

    /**
     * Becareful
     * @param string $id
     * @param string $permission
     * @return Entity |ApiProblem
     */
    private function getEntityAndCheckPermission($id, $permission)
    {
        $entity = $this->dataService->find($id);
        if (!$entity) {
            return new ApiProblem(404, 'Entity with ' . $id . ' was not found');
        }
        $result = $this->isAuthorized($entity, $permission, false);
        return ($result !== true ? $result : $entity);
    }


    /**
     * Fetch all or a subset of resources
     * Just admin can use default only, other specific case please implement by yourself
     *
     * @see Apigility/Doctrine/Server/Resource/AbstractResource.php
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        if (!$this->isAdmin()) {
            return new ApiProblem(401, 'Unauthorized');
        }
        try {
            return $this->dataService->fetchAll($this->getQueryParams(), null, null, $this->getCollectionClass());

        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
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
            $result = $this->getEntityAndCheckPermission($id, AclAuthorization::PERMISSION_UPDATE);
            if ($result instanceof ApiProblem) {
                return $result;
            }
            return $this->dataService->patch($result, $data);
        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
        }
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
        try {
            $result = $this->getEntityAndCheckPermission($id, AclAuthorization::PERMISSION_UPDATE);
            if ($result instanceof ApiProblem) {
                return $result;
            }

            return $this->dataService->update($result, $data);
        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
        }
    }

    /**
     * Get the Http Request object
     *
     * @return null|RequestInterface
     */
    public function getRequest()
    {
        return $this->event->getRequest();
    }

    /**
     * Get the uploaded files
     *
     * @return File
     */
    public function getFiles()
    {
        return $this->getRequest()->getFiles();
    }

    /**
     * Get the posted data
     *
     * @return Post
     */
    public function getPost()
    {
        return $this->getRequest()->getPost();
    }

    /**
     * Get query parameters from Web context for FetchAll method
     *
     * @return Array|mixed
     */
    protected function getQueryParams()
    {
        $resultParameters = array();

        /** @var ResourceEvent $event */
        $event = $this->getEvent();

        /** @var \Zend\Stdlib\Parameters $inputParameters */
        $inputParameters = $event->getRequest()->getQuery()->toArray();

        $this->debug('Parse query string : ' . print_r($inputParameters, true));

        $keyWords = array('page', 'size', 'sort', 'fields', 'extends');

        //query params
        foreach ($inputParameters as $key => $value) {
            if (!in_array($key, $keyWords)) {
                $resultParameters['query'][$key] = $value;
            }
        }

        //check sort
        $orderBy = array();
        if (isset($inputParameters['sort'])) {
            $sortFields = explode(',', $inputParameters['sort']);
            foreach ($sortFields as $field) {
                $field = trim($field);
                $order = 'ASC';
                if (String::startsWith($field, '-')) {
                    $order = 'DESC';
                    $field = trim(substr($field, '1', strlen($field) - 1));
                }

                if (String::startsWith($field, '+')) {
                    $order = 'ASC';
                    $field = trim(substr($field, '1', strlen($field) - 1));
                }

                if ($field) {
                    $orderBy[$field] = $order;
                }
            }
        }
        if (count($orderBy) > 0) {
            $resultParameters['orderBy'] = $orderBy;
        }

        $this->debug('Output parameters  : ' . print_r($resultParameters, true));
        return $resultParameters;
    }


    /**
     * @param \Exception $ex
     * @return ApiProblem
     */
    protected function processUnhandledException(\Exception $ex)
    {
        $this->error($ex->getMessage(), array('exception' => $ex));
        $this->error($ex->getTraceAsString(), array('exception' => $ex));

        if ($ex instanceof ValidationException) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => $ex->getValidationErrors(),
            ));
        } elseif ($ex instanceof EntityNotFoundException ||
            $ex instanceof DoctrineModuleNotFoundException
        ) {
            return new ApiProblem(404, $ex->getMessage());
        } elseif ($ex instanceof \Doctrine\DBAL\DBALException) {
            //do not expose database sensitive information to client
            return new ApiProblem(500, "Unknown exception. Please contact your System Administrator.");
        } else {
            $code = $ex->getCode();
            //http range
            $code = $code >= 200 && $code <= 599 ? $code : 500;
            return new ApiProblem($code, $ex->getMessage());
        }
    }


    /**
     * @return bool
     */
    public function isAdmin()
    {
        $identity = $this->getIdentity();
        if ($identity && $identity->isAdmin()) {
            return true;
        }
        return false;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @throws \Exception
     * @return string
     */
    public function getResourceId()
    {
        throw new \Exception('Not implement interface "ResourceInterface"');
    }
}
