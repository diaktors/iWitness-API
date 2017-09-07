<?php

namespace Api\V1\Controller;


use Zend\InputFilter\InputFilter;

class ValidationException extends \Exception
{
    private $validationErrors = array();

    /**
     * @param $errors
     * @return $this
     */
    public function setValidationErrors($errors)
    {
        $this->validationErrors = $errors;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @param ValidationResult $validationResult
     * @throws \Exception
     * @return \Api\V1\Controller\ValidationException
     */
    public static function create( $validationResult)
    {
        $exception = new ValidationException();

        if($validationResult instanceof ValidationResult){
            $exception->setValidationErrors($validationResult->getErrors());
        }elseif($validationResult instanceof InputFilter){
            $exception->setValidationErrors($validationResult->getMessages());
        }else{
             throw new \Exception('Invalid validation result parameter');
        }
        return $exception;
    }


}