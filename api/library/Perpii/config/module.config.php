<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    'service_manager' => array(
        'invokables' => array(
            'Perpii\\Hydrator\\Strategy\\CollectionLink' => 'Perpii\\Hydrator\\Strategy\\CollectionLink',
            'Perpii\\Doctrine\\Logging\\PhpErrorLogger' => 'Perpii\\Doctrine\\Logging\\PhpErrorLogger'
        ),

        'factories' => array(
            'Perpii\\OAuth2\\Adapter\\PdoAdapter' => 'Perpii\\OAuth2\\Factory\\PdoAdapterFactory',
            'Perpii\\Message\\EmailManager' => 'Perpii\\Message\\EmailManagerFactory',
            'Perpii\\Premailer\\Premailer' => 'Perpii\\Premailer\\PremailerFactory',
            'Perpii\\Message\\SmsManager' => 'Perpii\\Message\\SmsManagerFactory',
            'Perpii\\Message\\PushNotificationManager' => 'Perpii\\Message\\PushNotificationManagerFactory',
            'Perpii\\View\\ViewHelper' => 'Perpii\\View\\ViewHelperFactory',
            'FFMpeg\\FFMpeg' => 'Perpii\\FFMpeg\\FFMpegServiceFactory',
            'doctrine.cache.redis' =>'Perpii\\Doctrine\\Cache\\DoctrineRedisCacheFactory',
            'ZF\OAuth2\Service\OAuth2Server' => 'Perpii\OAuth2\Factory\OAuth2ServerFactory'
        ),
    ),
    'view_helpers' => array(
        'factories'=> array(
            'helper' => 'Perpii\\View\\ViewHelperFactory',
        )
    ),
);
