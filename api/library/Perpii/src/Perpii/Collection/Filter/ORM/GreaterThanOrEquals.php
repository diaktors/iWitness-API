<?php

namespace Perpii\Collection\Filter\ORM;

class GreaterThanOrEquals extends AbstractFilter
{
    public function filter($queryBuilder, $metadata, $option)
    {
        if (isset($option['where'])) {
            if ($option['where'] == 'and') {
                $queryType = 'andWhere';
            } elseif ($option['where'] == 'or') {
                $queryType = 'orWhere';
            }
        }

        if (!isset($queryType)) {
            $queryType = 'andWhere';
        }

        $format = null;
        if (isset($option['format'])) {
            $format = $option['format'];
        }

        $value = $this->typeCastField($metadata, $option['field'], $option['value'], $format);

        $parameter = uniqid('a');
        $queryBuilder->$queryType($queryBuilder->expr()->gte('row.' . $option['field'], ":$parameter"));
        $queryBuilder->setParameter($parameter, $value);
    }
}
