<?php

namespace Perpii\Message {

    use Exception;

    class PushNotificationManagerFactory
    {
        public function __invoke($services)
        {
            $config = $services->get('config');
            $logger = $services->get('Psr\\Log\\LoggerInterface');
            $notificationConfig = $config['pushNotification'];
            if(!$notificationConfig) {
                throw new Exception('Make sure you config for Push Config section in the Global Config file');
            }

            return new PushNotificationManager($notificationConfig, $logger);
        }
    }
}