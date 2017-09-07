<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 7/3/14
 * Time: 3:50 PM
 */

namespace Api\V1\Hydrator;


class ProspectHydrator extends BaseHydrator
{
    protected function getDefaultFields()
    {
        return array(
            'id',
            'email',
        );
    }
}