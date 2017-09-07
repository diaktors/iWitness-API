<?php
namespace Api\V1\Rest\Plan;

use Api\V1\Service\PlanService;
use ZF\ApiProblem\ApiProblem;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Resource\ResourceAbstract;

class PlanResource extends ResourceAbstract
{
    use PlanValidatorTrait;

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        return new ApiProblem(405, 'The POST method has not been defined');
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
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
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
        try {
            return $this->getPlanService()->fetchAll($this->getQueryParams(), null, null, $this->getCollectionClass());

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
            /** @var \Api\V1\Entity\Plan */
            $plan = $this->getPlanService()->find($id);
            if (!$plan) {
                return new ApiProblem(404, 'Plan with id ' . $id . ' was not found');
            }

            $result = $this->isAuthorized($plan, AclAuthorization::PERMISSION_UPDATE, false);
            if ($result !== true) {
                return $result;
            }

            $data = (array)$data;
            $inputFilter = $this->getUpdatingPlanFilter();
            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    array('validation_messages' => $inputFilter->getMessages())
                );
            }

            $filteredValues = $this->getInputFilteredValues($inputFilter, $data);
            $plan = $this->getPlanService()->updatePlan($plan, $filteredValues);
            return $plan;

        } catch (\Exception $ex) {
           return  $this->processUnhandledException($ex);
        }
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
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
     * @return PlanService
     */
    private function getPlanService()
    {
        return $this->dataService;
    }
}
