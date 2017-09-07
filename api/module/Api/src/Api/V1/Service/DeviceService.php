<?php

namespace Api\V1\Service {

    use Api\V1\Entity\Device;
    use Api\V1\Entity\User;
    use Api\V1\Entity\UserDevice;
    use Doctrine\ORM\EntityManager;
    use Perpii\Message\PushNotificationManager;
    use Webonyx\Util\UUID;

    class DeviceService extends ServiceAbstract
    {
        const ENTITY_CLASS = 'Api\V1\Entity\Device';

        /** @var array|null */
        private $config = null;

        /** @var $notificationManager PushNotificationManager */
        private $notificationManager = null;

        public function __construct(
            array $config,
            EntityManager $entityManager,
            PushNotificationManager $notificationManager,
            $logger)
        {
            parent::__construct($entityManager, $logger);

            $this->config = $config;
            $this->notificationManager = $notificationManager;
        }

        /**
         * Insert data for device and user device
         *
         * @param  mixed $data
         * @param $user
         * @return Device
         */
        public function insertUserDevice($data, $user)
        {
            $device = $this->getRepository()->findBy(array('token' => $data['token']));

            // we need to check if device is existing or not
            // if not we will insert both device and user device
            // else we only need to update user device
            if ($device) {
                $device = $this->existedDevice(is_array($device) ? $device[0] : $device, $user);
            } else {
                $device = $this->createUserDevice($data, $user);
            }

            // commit everything to database
            $this->entityManager->flush();

            return $device;
        }

        /**
         * Create data for only user device
         * we don't insert new record for device in this case
         * //todo: don't understand, what is the responsibility of this function? it looks like check existing but not really
         * @param $device
         * @param $user
         * @return Device
         */
        private function existedDevice($device, $user)
        {
            // populate data for UserDevice
            $userDevice = new UserDevice(UUID::generate());
            $userDevice->setDevice($device);
            $userDevice->setUser($user);
            $this->entityManager->persist($userDevice);

            return $device;
        }

        /**
         * Create data for both device and user device
         *
         * @param $data
         * @param $user
         * @return Device
         */
        private function createUserDevice($data, $user)
        {
            // populate data for Device
            $data['created'] = time();
            $device = new Device(UUID::generate());
            $this->hydrator->hydrate($data, $device);
            $this->entityManager->persist($device);

            // populate data for UserDevice
            $userDevice = new UserDevice(UUID::generate());
            $userDevice->setDevice($device);
            $userDevice->setUser($user);
            $this->entityManager->persist($userDevice);

            return $device;
        }

        /**
         * @param User $user
         * @return Array of Devices
         */
        public function findByUser(User $user)
        {
            return $this->getRepository()->findByUser($user);
        }
    }
}