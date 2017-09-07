<?php

namespace Perpii\Log\Handler;

use Monolog\Handler\StdoutHandler;
use Monolog\Logger;
use Monolog\Formatter\NoColorLineFormatter;

class StdHandler extends StdoutHandler
{

    /**
     * @param tring $stream
     * @param bool|int $level
     * @param bool $bubble
     */
    public function __construct($stream, $level = Logger::DEBUG, $bubble = true)
    {
        $this->validateStream($stream);
        parent::__construct($stream, $level, $bubble);
    }

    protected function validateStream($stream)
    {
        $allowStds = array('php://output', 'php://stderr', 'php://stdout');

        if (!in_array($stream, $allowStds)) {
            throw new \Exception('Allow std should be ' . json_encode($allowStds) . '. Input std ' . $stream);
        }
    }

    /**
     * @return NoColorLineFormatter|\Monolog\Handler\FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new NoColorLineFormatter(self::FORMAT);
    }
}