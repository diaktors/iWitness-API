<?php
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
}

date_default_timezone_set('GMT');

include_once(APPLICATION_PATH . '/vendor/autoload.php');

use  Zend\Stdlib\ArrayUtils;


$appConfig = include APPLICATION_PATH . '/config/autoload/global.php';
if (file_exists(APPLICATION_PATH . '/config/autoload/local.php')) {
    $appConfig = ArrayUtils::merge($appConfig, include APPLICATION_PATH . '/config/autoload/local.php');
}


$params = $appConfig['doctrine']['connection']['orm_default']['params'];

$doctrine = array(
    'name' => 'Doctrine Migrations',
    'migrations_namespace' => 'DoctrineMigrations',
    'table' => 'doctrine_migration_versions',
    'migrations_directory' => './migrations',
    'db_configuration' => array(
        'host' => $params['host'],
        'dbname' => $params['dbname'],
        'user' => $params['user'],
        'password' => $params['password'],
        'driver' => 'pdo_mysql',
    )
);

return $doctrine;


