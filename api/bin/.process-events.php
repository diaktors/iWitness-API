#!/usr/bin/php
<?php
require dirname(__FILE__) . '/Bootstrap.php';

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use  Symfony\Component\Process\Process;

class ProcessEvents implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    //todo: move to global config
    private $config = array(
        'request_frequency' => 2,
        'max_processes' => 1,
        'time_out' => 180
    );

    private $processes = array();

    /**
     * @var  \Zend\ServiceManager\ServiceManager;
     */
    private $serviceManager = null;

    /** @var \Api\V1\Service\EventService */
    private $eventService = null;

    /** @var EntityManager */
    private $entityManager = null;

    /**
     * constructor
     */
    public function __construct()
    {
        Bootstrap::initializeLogger('logger-video');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));
        $this->eventService = $this->serviceManager->get('Api\V1\Service\EventService');
        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
    }

    /**
     * Start deamon process
     */
    public function run()
    {
        $this->debug("Started IWITNESS daemon for event processing");

        while (true) {
            try {
                $this->entityManager->getConnection()->connect();
                $pendingEvents = $this->eventService->fetchForProcessing($this->config['max_processes']);
                $total = count($pendingEvents);
                if ($total > 0) {
                    $this->debug("Start process " . $total . ' event(s)');
                }

                foreach ($pendingEvents as $event) {
                    /** @var  \Api\V1\Entity\Event $event */
                    $id = $event->getId();

                    $this->debug("Starting processing of event: $id");
                    $process = new Process('php application.php event "' . $id . '" 1');
                    $process->setWorkingDirectory(APPLICATION_PATH . '/bin');
                    $process->setTimeout($this->config['time_out']);
                    $this->processes[$id] = $process;
                    $process->start();
                }
                while ($this->running()) {
                    usleep(500000);
                }

                if ($total > 0) {
                    $this->debug("End of process " . $total . ' event(s)');
                }
                $this->entityManager->getConnection()->close();

            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            sleep($this->config['request_frequency']);
        }
    }

    /**
     * @return bool
     */
    private function running()
    {
        $this->cleanup();
        return !empty($this->processes);
    }

    /**
     * Clean up finished processes
     */
    private function cleanup()
    {
        foreach ($this->processes as $pid => &$process) {
            /** @var Process $process */
            if (!$process->isRunning()) {
                if (!$process->isSuccessful()) {
                    $this->debug('There is an error when process event ' . $process->getErrorOutput());
                    $this->eventService->markProcessingError($pid, $process->getErrorOutput());
                } else {
                    $this->debug('Processed event ' . $pid . 'successfully');
                    $this->debug($process->getOutput());
                }
                $process->stop();
                unset($this->processes[$pid]);
            }
        }
    }


    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}

$jobQueue = new ProcessEvents();
$jobQueue->run();


