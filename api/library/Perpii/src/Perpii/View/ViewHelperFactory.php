<?php

namespace Perpii\View;
use Zend\View\Helper\AbstractHelper;

class ViewHelperFactory
{
    public function __invoke($sm)
    {
        return new ViewHelper($sm);
    }
}