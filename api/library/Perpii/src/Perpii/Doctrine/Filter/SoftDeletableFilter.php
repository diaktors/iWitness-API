<?php

namespace Perpii\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeletableFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface('\Perpii\Doctrine\Filter\SoftDeletable')) {
            return "";
        }
        return $targetTableAlias . '.deleted IS NULL ';
    }
} 