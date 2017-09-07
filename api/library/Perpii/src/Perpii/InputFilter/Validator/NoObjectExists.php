<?php


namespace Perpii\InputFilter\Validator;

class NoObjectExists extends ObjectExists
{
    /**
     * Error constants
     */
    const ERROR_OBJECT_FOUND = 'objectFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_OBJECT_FOUND => "An object matching '%value%' was found",
    );

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        if (!parent::isValid($value)) {
            return true;
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
