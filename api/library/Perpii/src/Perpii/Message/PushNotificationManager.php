<?php

namespace Perpii\Message {

    use Exception;

    class PushNotificationManager extends MessageManagerAbstract
    {

        const ANDROID_MODEL = 'Android';
        const IPHONE_MODEL  = 'IPhone';

        const PROFILE_UPDATED = 'ProfileUpdated';
        /**
         * @var string
         */
        private $model;

        /**
         * @var string
         */
        private $deviceToken;
        /**
         * @var string
         */
        private $message;

        /**
         * Set device token
         *
         * @param $deviceToken
         * @return PushNotificationManager
         */
        public function setDeviceToken($deviceToken)
        {
            $this->deviceToken = $deviceToken;

            return $this;
        }

        /**
         * Set device model [Android|Iphone]
         * @param $model
         * @return $this
         */
        public function setModel($model)
        {
            $this->model = $model;

            return $this;
        }

        /**
         * Set message
         *
         * @param $message
         * @return PushNotificationManager
         */
        public function setMessage($message)
        {
            $this->message = $message;

            return $this;
        }

        /**
         * Send message to the target
         *
         * @return void
         */
        public function send()
        {
            try {
                //String compare-insensitive case: 'android', 'Android', 'iPhone', 'IPhone'
                if (strcasecmp($this->model, self::ANDROID_MODEL) == 0) {
                    $this->sendPushNotificationToGCM();
                } else if (strcasecmp($this->model, self::IPHONE_MODEL) == 0) {
                    $this->sendPushNotificationToApple();
                }
            } catch (Exception $ex) {
                $this->error($ex->getMessage(), array('exception' => $ex));
            }
        }

        /**
         * Get stream socket client
         *
         * @throws \Exception
         * @return object
         */
        private function getStreamSocketClient()
        {
            // get all parameters from the config
            $iosConfig    = $this->config['IOS'];
            $securityFile = $iosConfig['key'];
            $passPhrase   = $iosConfig['passPhrase'];
            $gatewayUrl   = $iosConfig['notificationUrl'];

            $this->logger->info('Starting to establish connection to APNS');
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $securityFile);
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passPhrase);

            // Open a connection to the APNS server
            $client = stream_socket_client(
                $gatewayUrl, $err,
                $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            if (!$client)
                throw new \Exception("Failed to connect: $err $errstr");

            $this->logger->info('Connected to APNS');

            return $client;
        }

        /**
         * Encoding the message with the input device token and message
         *
         * @return string
         */
        private function encodeMessage()
        {
            $this->logger->info('Encode the message');
            // Create the payload body
            $body['aps'] = array(
                'alert' => $this->message,
                'sound' => 'default'
            );
            // Encode the payload as JSON
            $payload = json_encode($body);

            return chr(0) . pack('n', 32) . pack('H*', $this->deviceToken) . pack('n', strlen($payload)) . $payload;
        }

        private function sendPushNotificationToApple()
        {
            $contextStream = $this->getStreamSocketClient();
            $msg           = $this->encodeMessage();
            // Send it to the server
            $result = fwrite($contextStream, $msg, strlen($msg));
            if (!$result) {
                $this->error('Message not delivered');
            } else {
                $this->info('Message successfully delivered');
            }

            // Close the connection to the server
            fclose($contextStream);
        }

        /**
         * Send push notifications to Google Cloud Message
         * @throws \Exception
         * @return mixed
         */
        private function sendPushNotificationToGCM()
        {
            $androidConfig = $this->config['Android'];

            $fields = array(
                'registration_ids' => array($this->deviceToken),
                'data'             => array('message' => $this->message),
            );

            $headers = array(
                'Authorization: key=' . $androidConfig['publicKey'],
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $androidConfig['notificationUrl']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->error(curl_error($ch));
            }

            if ($result === false) {
                throw new \Exception("Failed to connect GCM service");
            }

            curl_close($ch);

            return $result;
        }
    }
}