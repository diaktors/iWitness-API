<?php

return array(
    'zf-mvc-auth' => array(
        'authentication' => array(),
    ),
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'options' => array(
                    'route' => '/oauth',
                ),
            ),
        ),
    ),
    'aws' => array(
        'key' => 'AKIAIPX4RFT6QBHD6WCQ',
        'secret' => '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',
        'region' => 'us-east-1', //should choose correct AWS region for this via: http://docs.aws.amazon.com/general/latest/gr/rande.html
        'account' => '598468219440',
        'useAwsTransport' => true,
        'useS3Storage' => true
    ),
    'nexmo' => array(
        'key' => '8beecbc3',
        'secret' => '62ad0fd9',
        'from' => '17203611355'
    ),

    'web' => array(
        'baseUrl' => 'https://www.iwitness.com',
        'secureBaseUrl' => 'https://www.iwitness.com',
        'corsDomain' => 'iwitness.com',
        'premailerCssPath' => APPLICATION_PATH . '/module/Api/styles/compiled/mail.css',
        'fromAddress' => 'info@iwitness.com',
        'infoEmailAddress' => 'info@iwitness.com',
        'fromName' => 'Member Services - IWITNESS',

        'fallback' => array(
            'server' => array(
                'host' => 'outlook.office365.com', //should change this to IMAP of email address web->fromAddress above
                'password' => 'P@ssword', //password for user account web->fromAddress above
                'port' => 993,
                'folder' => 'INBOX',
                'useSsl' => true,
            ),
        )
    ),

    'api' => array(
        'baseUrl' => 'https://api.iwitness.com',
    ),

    'zf-oauth2' => array(
        'storage' => 'Perpii\\OAuth2\\Adapter\\PdoAdapter',
        'storage_settings' => array(
            'field_id' => 'id',
            'user_table' => 'user',
            'field_username' => 'phone',
            'field_userid' => 'id',
            'subscription_table' => 'subscription'
        ),
        'digest_auth_realm' => 'IWITNESS API', //support old md5 encryption
        'access_lifetime' => 5184000, // temporary to put the token is expired in 1 day
        'refresh_token_lifetime' => 7776000, //90 days
        'always_issue_new_refresh_token' => true, //please don't change this without changing clients accordingly. For web it's ok but mobile may cause issue
    ),

    'redis-cache' => array( //setting this option when you want to use redis as caching layer for doctrine
        'adapter' => array(
            'name' => 'redis',
            'options' => array(
                'server' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                ]
            )
        ),
    ),

    'doctrine' => array(
        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host' => 'iw-db-01.ce1mskef1ivg.us-east-1.rds.amazonaws.com',
                    'port' => '3306',
                    'user' => 'iwitness',
                    'password' => 'hAsw6d',
                    'dbname' => 'iwitness_api',
                )
            )
        ),
        'driver' => array(
            'Api\V1\Entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',

                'cache' => 'array', //change to redis or memcache  to have better performance
                //'cache' => 'redis',   //change to this when redis server above was set

                'paths' => array(
                    APPLICATION_PATH . '/module/Api/src/Api/V1/Entity'
                ),
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Api\V1\Entity' => 'Api\V1\Entity',
                ),
            ),
        ),

        // See http://docs.doctrine-project.org/en/latest/reference/configuration.html
        'configuration' => array(
            'orm_default' => array(
                'metadata_cache' => 'array', // default option
                //'metadata_cache'    => 'redis', //change to this when redis server above was set

                'query_cache' => 'array', // default option
                //'query_cache'       => 'redis', //change to this when redis server above was set

                'result_cache' => 'array', // default option
                //'result_cache'      => 'redis', //change to this when redis server above was set

                // Generate proxies automatically (turn off for production)
                'generate_proxies' => false,

                // SQL filters. See http://docs.doctrine-project.org/en/latest/reference/filters.html
                'filters' => array(
                    'soft-deletable' => 'Perpii\Doctrine\Filter\SoftDeletableFilter'
                ),

                'types' => array(
                    'uuid' => 'Perpii\Doctrine\Type\UUID',
                    'bit-field' => 'Perpii\Doctrine\Type\BitField',
                ),
            )
        ),
    ),

    'ffmpeg' => array(
        'default' => array(
            'configuration' => array(
                'timeout' => null,
                'ffmpeg' => array(
                    'threads' => 4,
					'timeout' => 24000,
					'binaries' => array('ffmpeg', 'avconv')
                ),
                'ffprobe' => array(
                    'timeout' => 30,
                    'binaries' => array('ffprobe', 'avprobe')
                ),
            )
        )
    ),
    'photos' => array(
        's3' => array(
            'baseDir' => 's3://perpcast/userpics', //please grant permission on this folder
            'cacheDir' => '/volumes/data/photos/cache', //please grant permission on this folder
            'size' => array(
                'default' => array(
                    'width' => '200',
                    'quality' => '70'
                )
            )
        ),
        'dev' => array(
            'baseDir' => '/volumes/data/photos',
            'cacheDir' => '/volumes/data/photos/cache', //please grant permission on this folder
            'size' => array(
                'default' => array(
                    'width' => '200',
                    'quality' => '70'
                )
            )
        ),
    ),

    'events' => array(
        'trashDir' => '/volumes/data/events/trash', //please grant permission on this folder
        'cacheDir' => '/volumes/data/events/cache', //please grant permission on this folder
        'maxAttempted' => 3,
    ),

    'assets' => array(
        's3' => array(
            'baseDir' => 's3://perpcast/assets',
            'trashDir' => 's3://perpcast/assets_trash',
            'cacheDir' => '/volumes/data/assets/cache', //please grant permission on this folder
            'processingQueue' => 'process_assets',
            'maxAttempted' => 3,
            'maxFileSize' => 5242880, //5MB, please check if upload_max_filesize in php.ini is larger than this
            'audioEncode' => 'libfdk_aac' // or aac | libfaac | libfdk_aac | libvo_aacenc see https://trac.ffmpeg.org/wiki/AACEncodingGuide to setup it correctly
        ),
        'dev' => array(
            'baseDir' => '/volumes/data/assets', //please grant permission on this folder
            'trashDir' => '/volumes/data/assets/trash', //please grant permission on this folder
            'cacheDir' => '/volumes/data/assets/cache', //please grant permission on this folder
            'processingQueue' => 'process_assets',
            'maxFileSize' => 5242880, //5MB, please check if upload_max_filesize in php.ini is larger than this
            'maxAttempted' => 3,
            'audioEncode' => 'libfdk_aac' // or aac | libfaac | libfdk_aac | libvo_aacenc see https://trac.ffmpeg.org/wiki/AACEncodingGuide to setup it correctly
        )
    ),

    'paymentGateWays' => array(
        'webCheckout' => array(
            'sandbox' => 0, //change to 1 in development environment
            'getWay' => 'Authorize.Net', // 'PayPal' or 'Authorize.Net'
            'description' => 'IWITNESS.com subscription',

            'Authorize.Net' => array(
                'loginId' => '6Lr3f3LF',
                'transactionKey' => '6683F39xK55FekxA',
                'log' => '/volumes/log/api/authorize-net.log'
            ),

            'PayPal' => array(
                //'acct1.UserName' => 'hung-facilitator_api1.webonyx.com',
                //'acct1.Password' => '1404701709',
                //'acct1.Signature' => 'AHn-E0Oiyh57zSk5ahaZmOU5b5tgA847Ec-atWW7S51ZM-sFT8RPWoot',
                //see https://developer.paypal.com/docs/integration/direct/make-your-first-call/
                //https://developer.paypal.com/docs/integration/direct/accept-credit-cards/
            ),
        ),
        'appleStore' => array(
            'verifySandboxReceiptUrl' => 'https://sandbox.itunes.apple.com/verifyReceipt', //this option for testing only
			'verifyReceiptUrl' => 'https://buy.itunes.apple.com/verifyReceipt', //change to this option when go on production
			'sharedSecret' => '03f38ffacb414b399b1132ceafd93162'
        ),
		'googlePlay' => array(
			//This option for prodution
            'com.iwitness.android' => array(
                'publicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnQEMIgugnsfl6jdb05FBKE//2rCc2wgUnq26S8pMV6LrJKT+IP2Sv1t/4IfvGu2VxSdK2Kzqe9FYNstfY/ovyQ9oKt1gddOTDq1TEuZ0Pv3TTDXGGMRjQcbC2rPrsloekMPt8SKRHC8XaU9XmLEvZUOmJlI2rkTPTe6pAevSfOGRO4LADiCFjmfpP69HGGPZjc6VyxbsbmWkzF2Xg8PdRox4/KnlB59Wr7vZ5j9/g18izWQCFM3cHTX169Am5uAY8YG5EjGS/kUtjV2vUOsq9r0OFQ96hZ2Nk6nOWIAHtVieByx/rbgm1JGuMhNvgNqhxCdpCy+an8QPMVhoVdzaAQIDAQAB',
                'clientID' => '325547727416-430kouj40ei4bka7i52vcq2og9ckmk72.apps.googleusercontent.com',
                'clientSecret' => 'rqZIuQzovAtHZl82I1liDMRE',
                'developerKey' => 'AIzaSyAno_8gmsoZy2yv-UNuleIu6o5tp_sHxj4',
                'refreshToken' => '1/vFwo9xEMVDLyc1WjMCJrrwSL8fZd3X9xGTU94qrX6J0',
                'redirectUri' => 'https://www.iwitness.com/oauth2callback',
                'devAccountName' => 'greg@perpcast.com',
                'scopes' => array('https://www.googleapis.com/auth/androidpublisher')
            ),
			//This option for new production
            'com.iwitness.androidapp' => array(
                'publicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo2pUlfPZuU/xNi8+wb35PsOjNbCsZnJDdWInYzoLKHiQx7yQtNjjxk344c3NpkjAmDYaC6mEX3Ibgfalc5/yVPKiR5qIJEo6XCfj/pWr5TmC8ZphLpI0LUdd3rMQm7atHFomfV0/WE1pr2deSFgrO+ehaGLNa8USxdpcSWHfbLpN52q3W07zU1HnoToOaSN0fGHuKKxZaNvUuziLxprQVTqylwHW8v8thEuamNhpvtLqDyHrFLgoukEflCEiw25IU7ZlOaHrqwyKhSuOdoYs7EyPdcCcOr7eHpVRbnTyTPud1GxfATxF6ibVwvR9WF4MEVnPiAGlUQ7Fv2wX2S89lwIDAQAB',
               // 'clientID' => '325547727416-17nogdeq4fvse6t15afuv5h2uvifledj.apps.googleusercontent.com',
                'clientID' => '325547727416-430kouj40ei4bka7i52vcq2og9ckmk72.apps.googleusercontent.com',
                //'clientSecret' => 'i9VAihd397GshabOlDlWGxGK',
                'clientSecret' => 'rqZIuQzovAtHZl82I1liDMRE',
                'developerKey' => 'AIzaSyAno_8gmsoZy2yv-UNuleIu6o5tp_sHxj4',
                //'developerKey' => 'AIzaSyCJunZypUpOXFz7XdYvGxf3f9AlQY_4_mw',
                //'refreshToken' => '1/OXWH9DDhw5tN0yGYf194PqccF0_1Qia9JuZvfUqD9Z0',
                //'refreshToken' => '1/ttDkJVPoJJ0HjGz6JZ4qKYoFr8yVjw5v0DSsMJs8Cih_XhEowuUxMyZTwm_-j6qq', //2nd
                'refreshToken' => '1/0x2BNj59RZSFRbkAcuTWMO-4zGmfHyPNPI116XGGkgg',
               // 'refreshToken' => '1/vFwo9xEMVDLyc1WjMCJrrwSL8fZd3X9xGTU94qrX6J0', //1st
                'redirectUri' => 'https://www.iwitness.com/oauth2callback',
                'devAccountName' => 'greg@perpcast.com',
                //'devAccountName' => 'teri@iwitness.com',
                'scopes' => array('https://www.googleapis.com/auth/androidpublisher')
			),
			//This option for testing
            'com.iwitness.androidtest' => array(
                'publicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnJ7X8f7UkxeQ2wrVyvqRy1E3AW0pkoXvDzk2Z6EWcgvq/F/s0K/W1teBySahA1SCrjjrSIKxucFEjqwfSmmDdYGLGwFA9zYoC7j8+HwVJDn4k4iZjaKrBeasB32gIiAJx16BxXzy/BrH3EKAh0eG1zYWsU90BbiyspQzDbZIvmnPCUbtGuYovhs9tK2l+UGKCklyfrZXJAi06iAdfhllSzVCEgaAHPhou2LSNKjykqRR4jqu/yxpE02VCA1bW8sh7GvuGU+IKu9QoKEybR/S+xWrb4kOCiblPR/lKKoeTqc5Ya8oU/nvK8cYEpWV6CYHDfibtdIagTAdrWvWvlx15QIDAQAB',
                'clientID' => '325547727416-430kouj40ei4bka7i52vcq2og9ckmk72.apps.googleusercontent.com',
                'clientSecret' => 'rqZIuQzovAtHZl82I1liDMRE',
                'developerKey' => 'AIzaSyAno_8gmsoZy2yv-UNuleIu6o5tp_sHxj4',
                'refreshToken' => '1/vFwo9xEMVDLyc1WjMCJrrwSL8fZd3X9xGTU94qrX6J0',
                'redirectUri' => 'https://www.iwitness.com/oauth2callback',
                'devAccountName' => 'greg@perpcast.com',
                'scopes' => array('https://www.googleapis.com/auth/androidpublisher')
			)
        )
    ),

    'pushNotification' => array(
        'IOS' => array(
            'passPhrase' => 'P@ssw0rd',
            'key' => APPLICATION_PATH . '/bin/ck.pem',
            'notificationUrl' => 'ssl://gateway.sandbox.push.apple.com:2195' // we have to change it in the production environment
        ),
        'Android' => array(
            'notificationUrl' => 'https://android.googleapis.com/gcm/send',
            'publicKey' => 'AIzaSyAno_8gmsoZy2yv-UNuleIu6o5tp_sHxj4',
            'senderId' => '213542198755'
        )
    ),

    'logger' => array(
        'handlers' => array(

            'logger-main' => array(
                'name' => 'Perpii',
                'adapters' => array(
                    'standard-file' => array(
                        'handler' => '\Monolog\Handler\StreamHandler',
                        'options' => array(
                            'output' => '/volumes/log/api/api.log', //please grant permission on this folder
                            'permission' => 0777, // 0644 only for owner read/write. Should change to 777 if share same log file
                        ),
                        'level' => \Monolog\Logger::DEBUG,
                        'enabled' => true
                    ),
                )
            ),
            'logger-video' => array(
                'name' => 'Perpii',
                'adapters' => array(
                    'standard-file' => array(
                        'handler' => '\Monolog\Handler\StreamHandler',
                        'options' => array(
                            'output' => '/volumes/log/event.log', //please grant permission on this folder
                            'permission' => 0777, // 0644 only for owner read/write. Should change to 777 if share same log file
                        ),
                        'level' => \Monolog\Logger::DEBUG,
                        'enabled' => true
                    ),
                )
            ),
            'logger-email' => array(
                'name' => 'Perpii',
                'adapters' => array(
                    'standard-file' => array(
                        'handler' => '\Monolog\Handler\StreamHandler',
                        'options' => array(
                            'output' => '/volumes/log/api/api.log', //please grant permission on this folder
                            'permission' => 0777, // 0644 only for owner read/write. Should change to 777 if share same log file
                        ),
                        'level' => \Monolog\Logger::DEBUG,
                        'enabled' => true
                    ),
                )
            ),
            'logger-subscription' => array(
                'name' => 'Perpii',
                'adapters' => array(
                    'standard-file' => array(
                        'handler' => '\Monolog\Handler\StreamHandler',
                        'options' => array(
                            'output' => '/volumes/log/api/api.log', //please grant permission on this folder
                            'permission' => 0777, // 0644 only for owner read/write. Should change to 777 if share same log file
                        ),
                        'level' => \Monolog\Logger::DEBUG,
                        'enabled' => true
                    ),
                )
            ),
        ),
    ),


    //Cross-origin resource sharing http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
    //mainly used for image/video upload
    'zfr_cors' => array(
        /**
         * Set the list of allowed origins domain with protocol.
         */
        'allowed_origins' => array(
            'http://www.iwitness.com',
            'https://www.iwitness.com',
            'chrome-extension://fhbjgbiflinjbdggehcddcbncdddomop'
        ),

        /**
         * Set the list of HTTP verbs.
         */
        'allowed_methods' => array('GET', 'POST'),

        /**
         * Set the list of headers. This is returned in the preflight request to indicate
         * which HTTP headers can be used when making the actual request
         */
        'allowed_headers' => array('Authorization'),

        /**
         * Set the max age of the preflight request in seconds. A non-zero max age means
         * that the preflight will be cached during this amount of time
         */
        'max_age' => 120,

        /**
         * Set the list of exposed headers. This is a whitelist that authorize the browser
         * to access to some headers using the getResponseHeader() JavaScript method. Please
         * note that this feature is buggy and some browsers do not implement it correctly
         */
        // 'exposed_headers' => array(),

        /**
         * Standard CORS requests do not send or set any cookies by default. For this to work,
         * the client must set the XMLHttpRequest's "withCredentials" property to "true". For
         * this to work, you must set this option to true so that the server can serve
         * the proper response header.
         */
        'allowed_credentials' => true,
    ),


    //system turning part
    'sendFile' => array(
        //make sure apache X-SendFile plugin  was installed on server when you turn this option on. see https://tn123.org/mod_xsendfile/
        //XSendFilePath in apache vhost.config must be point to media files above (ex: asset, event, photo)
        //XSendFilePath could not be set in .httpaccess, must setting it in apache, see https://github.com/nmaier/mod_xsendfile/issues/17
        'useXSendFileForNonByteRangeRequest' => false,
        //Must test xSendFile work properly with http byte range mode. see http://greenbytes.de/tech/webdav/draft-ietf-httpbis-p5-range-latest.html
        'useXSendFileForByteRangeRequest' => false,
    ),

    //please make sure the impacting when changing these options
    'phpSetting' => array(
        'log_errors' => 1,
        'display_errors' => 0,
        'error_log' => '/volumes/log/api/php.log',
        'error_reporting' => E_ALL ^ E_NOTICE,
        'xdebug.max_nesting_level' => 200
    ),

    'view_manager' => array(
        'template_map' => array(
            'site/layout' => APPLICATION_PATH . '/module/Api/view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'application' => APPLICATION_PATH . '/module/Api/view/',
        ),
        'display_exceptions' => false,
        'display_not_found_reason' => false,
    ),

    'notification' => array(
        'xDaysBeforeExpire' => 7
    ),
);
