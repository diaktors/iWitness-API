<?php

use Zend\Loader\AutoloaderFactory;
use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;


/**
 * Test bootstrap, for setting up autoloading
 *
 * @subpackage UnitTest
 */
class Bootstrap
{
    /** @var  ServiceManager $serviceManager */
    protected static $serviceManager;

    public static function init()
    {
        static::initAutoloader();
    }

    protected static function initAutoloader()
    {
        date_default_timezone_set("GMT");

        chdir(dirname(__DIR__));

        // Decline static file requests back to the PHP built-in webserver
        if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
            return false;
        }

        if (!file_exists('vendor/autoload.php')) {
            throw new RuntimeException(
                'Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.'
            );
        }

        // Setup autoloading
        include 'vendor/autoload.php';

        if (!defined('APPLICATION_PATH')) {
            define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
        }

        $appConfig = include APPLICATION_PATH . '/config/application.config.php';

        if (file_exists(APPLICATION_PATH . '/config/development.config.php')) {
            $appConfig = ArrayUtils::merge($appConfig, include APPLICATION_PATH . '/config/development.config.php');
        }

        $smConfig = isset($configuration['service_manager']) ? $appConfig['service_manager'] : array();
        self::$serviceManager = new ServiceManager(new ServiceManagerConfig($smConfig));
        self::$serviceManager->setService('ApplicationConfig', $appConfig);
        self::$serviceManager->get('ModuleManager')->loadModules();
    }

    public static function getServiceManager()
    {
        //todo: should check service initialization
        return self::$serviceManager;
    }

    /**
     * @param $loggerName
     * @throws Exception
     */
    public static function initializeLogger($loggerName)
    {
        if (empty($loggerName)) {
            return;
        }

        $loggerFactory = new Perpii\Log\LoggerAbstractFactory();

        self::$serviceManager->addAbstractFactory($loggerFactory);

        $logger = self::$serviceManager->get($loggerName);
        if (!$logger) {
            $logger = self::$serviceManager->get('logger-main');
        }

        if(!$logger){
            throw new \Exception("Could not log logger, please check your configuration file");
        }

        self::$serviceManager->setService('Psr\\Log\\LoggerInterface', $logger);
    }
}

Bootstrap::init();
