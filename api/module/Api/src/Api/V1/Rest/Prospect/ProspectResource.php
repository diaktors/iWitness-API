<?php
namespace Api\V1\Rest\Prospect;

use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Resource\ResourceAbstract;
use ZF\ApiProblem\ApiProblem;

class ProspectResource extends ResourceAbstract
{
    use ProspectValidatorTrait;

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        try {
            $data = (array)$data;
            $inputFilter = $this->getCreatingProspectFilter();
            $inputFilter->setData($data);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            return $this
                ->getProspectService()
                ->insertOrUpdate($inputFilter->getValue('platform'), $inputFilter->getValue('email'));

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @return \Api\V1\Service\ProspectService
     */
    public function getProspectService()
    {
        return $this->dataService;
    }


    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_PROSPECT;
    }
}
