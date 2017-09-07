<?php

namespace Perpii\Util;

class ResourceHelper
{
    /**
     * @see Uri::$defaultPorts
     */
    private static $defaultPorts = array(
        'http'  => 80,
        'https' => 443,
    );

    private function __construct()
    {

    }

    /**
     * Processing the Uri to get full URI (schema, host, port)
     *
     * @param $resource
     * @internal param \Zend\Uri\Http $uri
     * @return string path
     */
    public static function getCurrentUri($resource)
    {
        if (!$resource) {
            return '';
        }

        /** @var \Zend\Uri\Http $uri */
        $uri = $resource
            ->getEvent()
            ->getRequest()
            ->getUri();

        $port = $uri->getPort();
        $scheme = $uri->getScheme();
        if (array_key_exists($scheme, self::$defaultPorts)
            && self::$defaultPorts[$scheme] == $port) {
            $port = null;
        }

        return $port == null
            ? "{$scheme}://{$uri->getHost()}"
            : "{$scheme}://{$uri->getHost()}:{$port}";
    }
}