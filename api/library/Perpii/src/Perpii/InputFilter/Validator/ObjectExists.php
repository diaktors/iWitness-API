<?php

namespace Perpii\InputFilter\Validator;

use Doctrine\Common\Collections\Criteria;

class ObjectExists extends \DoctrineModule\Validator\ObjectExists
{
    private $ignoreId = null;
    private $softDeleted = null;
    private $userId = null;

    public function __construct(array $options)
    {
        parent::__construct($options);

        if (isset($options['ignore_id'])) {
            $this->ignoreId = (string)$options['ignore_id'];
        }

        if(isset($options['soft_deleted'])) {
            $this->softDeleted = $options['soft_deleted'];
        }

        if (isset($options['user_id'])) {
            $this->userId = $options['user_id'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        if (!$this->ignoreId && !$this->softDeleted) {
            return parent::isValid($value);
        }

        $value = $this->cleanSearchValue($value);
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        if($this->ignoreId){
            $criteria->where($expr->neq('id', $this->ignoreId));
        }

        foreach ($value as $k => $v) {
            $criteria->andWhere($expr->eq($k, $v));
        }

        if (!empty($this->userId)) {
            $criteria->andWhere($expr->eq('userId', $this->userId));
        }

        if($this->softDeleted){
            $criteria->andWhere($expr->isNull('deleted'));
        }

        $criteria->setMaxResults(1);
        $match = $this->objectRepository->matching($criteria);

        if (($match) && ($match->count() > 0)) {
            return true;
        }

        $this->error(self::ERROR_NO_OBJECT_FOUND, $value);
        return false;
    }
} 