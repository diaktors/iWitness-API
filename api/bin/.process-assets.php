#!/usr/bin/php
<?php
require dirname(__FILE__) . '/Bootstrap.php';

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use  Symfony\Component\Process\Process;

class ProcessAsset implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    //todo: move to global config
    private $config = array(
        'request_frequency' => 2,
        'max_processes' => 2,
        'time_out' => 180
    );

    /**
     * @var array
     */
    private $processes = array();

    /**
     * @var  \Zend\ServiceManager\ServiceManager;
     */
    private $serviceManager = null;

    /** @var EntityManager */
    private $entityManager = null;

    /** @var \Api\V1\Service\AssetService */
    private $assetService = null;

    public function __construct()
    {
        Bootstrap::initializeLogger('logger-video');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\\Log\\LoggerInterface'));
        $this->assetService = $this->serviceManager->get('Api\V1\Service\AssetService');
        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
    }

    /**
     * Start Deamon process
     */
    public function run()
    {
        $this->debug("Started IWITNESS daemon for asset processing");

        while (true) {
            try {
                $this->entityManager->getConnection()->connect();
                $pendingAssets = $this->assetService->fetchForProcessing($this->config['max_processes']);
                $total = count($pendingAssets);

                if ($total > 0) {
                    $this->debug("Start process " . $total . ' asset(s)');
                }

                foreach ($pendingAssets as $asset) {
                    /** @var  \Api\V1\Entity\Asset $asset */
                    $id = $asset->getId();

                    $this->debug("Starting process  asset: $id");
                    $process = new Process('php application.php asset "' . $id . '" 1');
                    $process->setWorkingDirectory(APPLICATION_PATH . '/bin');
                    $process->setTimeout($this->config['time_out']);
                    $this->processes[$id] = $process;
                    $process->start();
                }
                while ($this->running()) {
                    usleep(500000);
                }

                if ($total > 0) {
                    $this->debug("End of process " . $total . ' asset(s)');
                }
                $this->entityManager->getConnection()->close();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            sleep($this->config['request_frequency']);
        }
    }

    /**
     * Is there process running
     * @return bool
     */
    private function running()
    {
        $this->cleanup();
        return !empty($this->processes);
    }

    /**
     * Check process status and clean it up when finish
     */
    private function cleanup()
    {
        foreach ($this->processes as $pid => &$process) {
            /** @var Process $process */
            if (!$process->isRunning()) {
                if (!$process->isSuccessful()) {
                    $this->debug('There is an error when process asset ' . $process->getErrorOutput());
                    $this->assetService->markProcessingError($pid, $process->getErrorOutput());
                } else {
                    $this->debug('Processed asset ' . $pid . 'successfully');
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


$jobQueue = new ProcessAsset();
$jobQueue->run();


