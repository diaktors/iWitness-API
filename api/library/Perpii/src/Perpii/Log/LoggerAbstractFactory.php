<?php
namespace Perpii\Log;

use Monolog\Logger;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoggerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'logger';

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {



        $config = $this->getConfig($services);
        if (empty($config) || !isset($config[$requestedName])) {
            return false;
        }



        return true;
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return Logger
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        $config = $config[$requestedName];

        $logger = new Logger($config['name']);

        if (count($config['adapters']) <= 0) {
            $logger->pushHandler(new NullHandler());
            return $logger;
        }

        foreach ($config['adapters'] as $adapter) {
            if ($adapter['enabled']) {
                $permission = isset($adapter['options']['permission']) ? $adapter['options']['permission'] : 0644;
                $handler = new $adapter['handler']($adapter['options']['output'], $adapter['level'], $permission);
                $logger->pushHandler($handler);
            }
        }


        return $logger;
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        //cache
        if ($this->config !== null) {
            return $this->config;
        }

        $this->config = array();
        $config = $services->get('Config');
        if ($config && isset($config[$this->configKey]['handlers'])) {
            $this->config = $config[$this->configKey]['handlers'];
        }

        return $this->config;
    }
}