<?php

namespace Perpii\Exception;

use Exception;

class DoctrineModuleNotFoundException extends \Exception
{
    public function __construct($className, $message = "", $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'No valid doctrine module is found for objectManager ' . $className;
        }
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
} 