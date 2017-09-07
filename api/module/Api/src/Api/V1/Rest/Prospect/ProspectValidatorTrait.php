<?php

namespace Api\V1\Rest\Prospect;
use Perpii\InputFilter\InputFilterTrait;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class CouponValidatorTrait
 * @package Api\V1\Rest\Coupon
 */
trait ProspectValidatorTrait
{
    use InputFilterTrait;

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    private function getCreatingProspectFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'platform', 'required' => true, 'allow_empty' => false))
            ->add(array(
                'name' => 'email', 'required' => true, 'allow_empty' => false,
                'validators' => array(array('name' => 'Zend\\Validator\\EmailAddress'),),
            ));
        return $inputFilter;
    }
}
