<?php

namespace Perpii\Doctrine\Cache;

use Doctrine\Common\Cache\RedisCache;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use  Redis;

class DoctrineRedisCacheFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RedisCache|mixed
     * @throws
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['redis-cache']['adapter']['options']['server'];
        if (extension_loaded('redis')) {
            $redis = new Redis();
            if ($redis->connect($config['host'], $config['port']) == false) {
                throw new \Exception('Could not connect to Redis server host=' . $config['host'] . ', port = ' . $config['port']);
            }

            $redisAdapter = new \Perpii\Doctrine\Cache\RedisCache();
            $redisAdapter->setRedis($redis);
            return $redisAdapter;

        } else {
            throw new \Exception('PHP Redis extension was not installed');
        }
    }
}