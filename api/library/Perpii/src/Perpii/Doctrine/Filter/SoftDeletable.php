<?php

namespace Perpii\Doctrine\Filter;

interface  SoftDeletable
{
    public function setDeleted($deleted);
}