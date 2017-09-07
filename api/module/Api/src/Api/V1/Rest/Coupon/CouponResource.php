<?php
namespace Api\V1\Rest\Coupon;

use Api\V1\Resource\ResourceAbstract;
use Api\V1\Rest\Coupon\CouponValidatorTrait;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\CouponService;
use Doctrine\ORM\QueryBuilder;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;


class CouponResource extends ResourceAbstract
{

    use CouponValidatorTrait;


    public function __construct(CouponService $couponService)
    {
        parent::__construct($couponService);
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
                return new ApiProblem(401, 'Unauthorized');
            }

            $data = (array)$data;

            $inputFilter = $this->getCreatingCouponFilter();
            $inputFilter->setData($data);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            return $this
                ->getCouponService()
                ->createCoupons($inputFilter->getValues());

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
            $params = $this->getQueryParams();
            if (!isset($params['query']['code'])) {
                $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_LIST_ALL);
                if ($result !== true) {
                    return new ApiProblem(401, 'Unauthorized');
                }
            }

            $select = null;
            if (isset($params['query']['codeString']) && isset($params['query']['codeStringSearchType'])) {
                $codeString = trim($params['query']['codeString']);
                $searchType = trim($params['query']['codeStringSearchType']);

                $searchPhase = null;
                switch ($searchType) {
                    case  'end with':
                        $searchPhase = $codeString . '%';
                        break;
                    case  'contains':
                        $searchPhase = '%' . $codeString . '%';
                        break;
                    case  'start with':
                    default:
                        $searchPhase = $codeString . '%';
                }
                $select = function (QueryBuilder &$queryBuilder) use (&$searchPhase) {
                    $queryBuilder
                        ->andWhere('row.codeString  LIKE :searchPhase OR  row.code LIKE :searchPhase')
                        ->setParameter('searchPhase', $searchPhase);
                };

                unset($params['query']['codeString']);
                unset($params['query']['codeStringSearchType']);
            }

            return $this
                ->getCouponService()
                ->fetchAll(
                    $params,
                    null,
                    $select,
                    $this->getCollectionClass()
                );

        } catch (\Exception $ex) {
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
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->dataService;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_COUPON;
    }
}
