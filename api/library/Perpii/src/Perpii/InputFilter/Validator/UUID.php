<?php

namespace Perpii\InputFilter\Validator;

use Zend\Validator\AbstractValidator;

class UUID extends AbstractValidator
{
    const INVALID = 'Provided input is not a valid UUID';
    const  PATTERN = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

    protected $messageTemplates = array(
        self::INVALID => "Provided input is not a valid UUID"
    );


    public function isValid($value)
    {
        if (!preg_match(self::PATTERN, $value, $match)) {
            $this->error(self::INVALID);
            return false;
        }
        return true;
    }
}