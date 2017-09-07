<?php
namespace Perpii\Exception;

use Exception;

class EntityNotFoundException extends \Exception
{
    public function __construct($entityId, $message = "", $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'Entity with id ' . $entityId . ' was not found';
        }
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}