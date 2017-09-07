<?php

namespace Api\V1\Controller;


class ValidationResult
{
    private $errors = array();

    private $values = array();


    /**
     * @return bool
     */
    public function  isValid()
    {
        return count($this->errors) <= 0;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $key
     * @param $error
     * @return $this
     */
    public function addError($key, $error)
    {
        if ($error) {
            if (!empty($key)) {
                if (isset($this->errors[$key]) && is_array($this->errors[$key])) {
                    $this->errors[$key][] = $error;
                } else {
                    $this->errors[$key] = array($error);
                }
            } else {
                $this->errors[] = $error;
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addValue($key, $value)
    {
        if (!empty($key)) {
            $this->values[$key] = $value;
        } else {
            $this->values[] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}