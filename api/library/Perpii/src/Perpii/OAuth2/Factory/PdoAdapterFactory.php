<?php

namespace Perpii\OAuth2\Factory;

use Perpii\OAuth2\Adapter\PdoAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;

class PdoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     * @return \ZF\OAuth2\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        //todo: can get configuration info from Doctrine config too, no need a separately config
        $config = $services->get('Config');

        if (!isset($config['doctrine']['connection']['orm_default']['params']) || empty($config['doctrine']['connection']['orm_default']['params'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'doctrine\'][\'connection\'][\'orm_default\'][\'params\'] for OAuth2 is missing'
            );
        }

        $params = $config['doctrine']['connection']['orm_default']['params'];

        $username = isset($params['user']) ? $params['user'] : null;
        $password = isset($params['password']) ? $params['password'] : null;

        $oauth2ServerConfig = array();
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        $oauth2ServerConfig['digest_auth_realm'] = isset($config['zf-oauth2']['digest_auth_realm']) ? $config['zf-oauth2']['digest_auth_realm'] : 'IWITNESS API';

        $connection = array(
            'dsn' => 'mysql:host=' . $params['host'] . ';dbname=' . $params['dbname'],
            'username' => $username,
            'password' => $password,
        );

        return new PdoAdapter($connection, $oauth2ServerConfig);
    }
}