<?php

namespace Api\V1\Rest\Emergency;

use Perpii\InputFilter\InputFilterTrait;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

trait EmergencyValidatorTrait
{
    use InputFilterTrait;

    private function getCreateInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'eventId', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'userId', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'msgtype', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'dialno', 'required' => false, 'allow_empty' => true));
        return $inputFilter;
    }
}
