<?php
namespace Perpii\Doctrine\Logging;

use Doctrine\DBAL\Logging\SQLLogger;


class PhpErrorLogger implements SQLLogger
{
    public function startQuery($sql, array $params = null, array $types = null)
    {
        error_log($sql . PHP_EOL);

        if ($params) {
            error_log('params=' . print_r($params, true));
        }

        if ($types) {
            error_log('type= ' . print_r($types, true));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {

    }
}