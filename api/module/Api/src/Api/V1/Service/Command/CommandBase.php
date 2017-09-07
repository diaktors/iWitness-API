<?php


namespace Api\V1\Service\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Api\V1\Service\BackgroundProcessInterface;

class  CommandBase extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /** @var \Api\V1\Service\BackgroundProcessInterface */
    protected $mediaServiceInterface = null;


    public function __construct($name = null, BackgroundProcessInterface $mediaServiceInterface, LoggerInterface $logger)
    {
        parent::__construct();
        $this->setLogger($logger);
        $this->mediaServiceInterface = $mediaServiceInterface;
    }


    /**
     * Configure the application
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Id  do you want to process?'
            )
            ->addArgument(
                'force',
                InputArgument::REQUIRED,
                'Do you want to force process?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $id = (string)$input->getArgument('id');
            $force = (bool)$input->getArgument('force');

            $this->logger->debug('Begin to process ' . $this->getName() . ' id =' . $id . ', with force ' . $force);
            $result = $this->mediaServiceInterface->process($id, $force);
            $this->logger->debug('Result of process ' . $this->getName() . ' id =' . $id . ' is ' . $result);
            $this->logger->debug('End of process ' . $this->getName() . ' id =' . $id . ', with force ' . $force);
            $this->mediaServiceInterface->markProcessingSuccess($id);

        } catch (\Exception $ex) {
            $this->mediaServiceInterface->markProcessingError($id, $ex->getMessage());
            $this->error($ex->getMessage());
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