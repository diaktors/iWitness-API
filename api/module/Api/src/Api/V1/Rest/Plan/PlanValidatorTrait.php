<?php

namespace Api\V1\Rest\Plan;

use Perpii\InputFilter\InputFilterTrait;


trait PlanValidatorTrait
{
    use InputFilterTrait;

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    private function getUpdatingPlanFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'name', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'description', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'price', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\I18n\\Validator\\Float'),
                ),))
            ->add(array('name' => 'member_price', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'length', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),));
        return $inputFilter;
    }
} 
