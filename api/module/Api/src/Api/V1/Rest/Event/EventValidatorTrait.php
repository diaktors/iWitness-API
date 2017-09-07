<?php

namespace Api\V1\Rest\Event;

use Perpii\InputFilter\InputFilterTrait;

trait EventValidatorTrait
{
    use InputFilterTrait;

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    protected function getInputFilters()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'id', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'displayName', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'name', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'initialLat', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'initialLong', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'processed', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'processed', 'required' => false, 'allow_empty' => true));

        return $inputFilter;
    }
} 