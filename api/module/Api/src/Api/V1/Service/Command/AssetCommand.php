<?php


namespace Api\V1\Service\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetCommand extends CommandBase
{

    /**
     * Configure the application
     */
    protected function configure()
    {
        $this
            ->setName('asset')
            ->setDescription('Command Asset');
        parent::configure();
    }
}