<?php

namespace Perpii\Collection\Filter;

interface FilterInterface
{
    public function filter($queryBuilder, $metadata, $option);
}
