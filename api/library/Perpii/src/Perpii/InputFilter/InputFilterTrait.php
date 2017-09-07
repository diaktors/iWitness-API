<?php

namespace Perpii\InputFilter;

use Zend\InputFilter\InputFilter;

trait InputFilterTrait
{

    /**
     * @return InputFilter
     */
    protected function getDefaultInputFilter()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(array(
            'name' => '*',
            'required' => false,
            'allow_empty' => true,
            'filters' => array(
                array('name' => 'Zend\\Filter\\StringTrim'),
                array('name' => 'Zend\\Filter\\HtmlEntities', 'options' => array('quotestyle' => ENT_NOQUOTES)),
            ),
        ));
        return $inputFilter;
    }


    /**
     * @param InputFilter $inputFilter
     * @param array $data
     * @return array
     */
    protected function getInputFilteredValues(InputFilter $inputFilter, array $data)
    {
        $values = array();
        foreach ($data as $name => $value) {
            if ($inputFilter->has($name)) {
                $values[$name] = $inputFilter->getValue($name);
            }
        }
        return $values;
    }

} 