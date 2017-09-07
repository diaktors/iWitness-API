<?php

namespace Perpii\Message {

    use Perpii\Log\LoggerTrait;
    use Psr\Log\LoggerAwareInterface;
    use Psr\Log\LoggerInterface;

    abstract class MessageManagerAbstract implements LoggerAwareInterface, SendMessageInterface
    {
        use LoggerTrait;

        /**
         * @var Array
         */
        protected $config;

        public function __construct(array $config, LoggerInterface $logger)
        {
            $this->setLogger($logger);
            $this->config = $config;
        }
    }
}