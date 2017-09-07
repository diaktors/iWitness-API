#!/usr/bin/php
<?php
require dirname(__FILE__) . '/../Bootstrap.php';

use Api\V1\Entity\User;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class ExpireNotice implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var  \Zend\ServiceManager\ServiceManager;
     */
    private $serviceManager = null;

    /** @var  \Perpii\Message\EmailManager */
    private $emailManager;

    /** @var \Api\V1\Service\UserService */
    private $userService;

    /** @var  \Perpii\Message\SmsManager */
    private $smsManager;

    /** @var  Perpii\Message\PushNotificationManager */
    private $notificationManager;

    /** @var Api\V1\Service\MessageService */
    private $messageService;

    /** @var  Doctrine\ORM\EntityManager */
    private $entityManager;

    /**
     * @var Api\V1\Service\DeviceService
     */
    private $deviceService;

    /** @var  array */
    private $config;


    public function __construct()
    {
        Bootstrap::initializeLogger('logger-email');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->config = $this->serviceManager->get('config');
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));

        $this->userService = $this->serviceManager->get('Api\V1\Service\UserService');
        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
        $this->messageService = $this->serviceManager->get('Api\\V1\\Service\\MessageService');
        $this->emailManager = $this->serviceManager->get('Perpii\\Message\\EmailManager');
        $this->smsManager = $this->serviceManager->get('Perpii\\Message\\SmsManager');
        $this->notificationManager = $this->serviceManager->get('Perpii\Message\PushNotificationManager');
        $this->deviceService = $this->serviceManager->get('Api\\V1\\Service\\DeviceService');

    }

    /**
     * Execute email sending
     */
    public function  run()
    {
        $this->debug('Begin to process expire notice message ');
        $this->processExpired();
        $this->processXDayExpire();
        $this->debug('End of process expire notice ');
    }


    private function processXDayExpire()
    {
        try {
            $xDay = $this->config['notification']['xDaysBeforeExpire'];
            $this->debug('Begin to process ' . $xDay . \Perpii\Util\String::pluralize($xDay, ' day', ' days') . ' expire  users ');
            $expireUsers = $this->userService->getXDaysExpire($xDay);
            $this->debug("total expire x days is " . count($expireUsers));
            /** @var User $user */
            foreach ($expireUsers as $user) {
                $this->notifyXDayExpire($user);
            }
            $this->debug('End of process x days expire  users');
        } catch (\Exception  $ex) {
            $this->error($ex->getMessage());
        }
    }


    private function processExpired()
    {
        try {
            $this->debug('Begin to process  expired  users ');
            $expireUsers = $this->userService->getExpired();
            $this->debug("total expired is " . count($expireUsers));

            /** @var User $user */
            foreach ($expireUsers as $user) {
                $this->notifyExpired($user);
            }
            $this->debug('End of process expired  users');
        } catch (\Exception  $ex) {
            $this->error($ex->getMessage());
        }
    }

    private function notifyXDayExpire(User $user)
    {
        try {
            $this->debug('Send x day  notice to user ' . $user->getEmail());

            $this->sendEmail($user, '/user/expire-notice-email.phtml');
            $this->sendSMS($user, '/user/expire-notice-sms.phtml');
            //removed as required from onsite. Will be open latter
            //$this->pushMessage($user, '/user/expire-notice-push.pthml');
            $user->setSubscriptionLastEmail(time());
            $this->updateUser($user);

        } catch (\Exception  $ex) {
            $this->error($ex->getMessage());
        }
    }

    /**
     * @param User $user
     */
    private function notifyExpired(User $user)
    {
        try {
            $xDay = $this->config['notification']['xDaysBeforeExpire'];
            $this->debug('Begin to process ' . $xDay . \Perpii\Util\String::pluralize($xDay, ' day', ' days') . ' expire  users ');

            $this->sendEmail($user, '/user/expired-notice-email.phtml');
            $this->sendSMS($user, '/user/expired-notice-sms.phtml');

            //removed as required from onsite. Will be open latter
            //$this->pushMessage($user, '/user/expired-notice-push.pthml');

            $user->setSubscriptionLastEmail(time());
            $this->updateUser($user);

            $this->debug('End of process x days expire  users');
        } catch (\Exception  $ex) {
            $this->error($ex->getMessage());
        }
    }


    /**
     * @param Api\V1\Entity\User $user
     * @param $template
     * @internal param \Api\V1\Entity\GiftCard $gift
     * @return bool
     */
    private function sendEmail(User $user, $template)
    {
        try {
            $vars = array('user' => $user);
            return $this
                ->emailManager
                ->setReceiver($user->getEmail())
                ->setTemplate($template)
                ->setTemplateData($vars)
                ->send();
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
        return false;
    }

    /**
     * @param User $user
     * @param $template
     * @return bool
     */
    private function sendSMS(User $user, $template)
    {
        try {
            $vars = array('user' => $user);

            return $this
                ->smsManager
                ->setReceiver($user->getPhone())
                ->setTemplate($template)
                ->setTemplateData($vars)
                ->send();

        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
        return false;
    }

    /**
     * @param User $user
     * @param $template
     * @return bool
     */
    private function pushMessage(User $user, $template)
    {
        try {
            $data = array('user' => $user);
            $content = $this->renderContent($data, $template);
            $this->messageService->insertUserMessage($content, $user);
            $this->sendNotification($user, $content);

            return true;
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
        return false;
    }


    /**
     * Send message to user
     * @param User $user
     * @param string $message
     */
    private function sendNotification(User $user, $message)
    {
        $devices = $this->deviceService->findByUser($user);
        foreach ($devices as $device) {
            /** @var \Api\V1\Entity\Device $device */
            if ($device && $device->getToken() && $message) {
                $this->notificationManager
                    ->setModel($device->getModel())
                    ->setMessage($message)
                    ->setDeviceToken($device->getToken())
                    ->send();
            }
        }
    }


    /**
     * @param array $data
     * @param $template
     * @return mixed|string
     */
    private function renderContent(array $data, $template)
    {
        /** @var PhpRenderer $viewRenderer */
        $viewRenderer = $this->serviceManager->get('ViewRenderer');

        $model = new ViewModel();
        $data = array_merge(
            array(
                'web'              => $this->config['web'],
                'secureBaseUrl'    => $this->config['web']['secureBaseUrl'],
                'baseUrl'          => $this->config['web']['baseUrl'],
                'apiBaseUrl'       => $this->config['api']['baseUrl'],
            ),
            $data
        );

        $model->setVariables($data);
        $model->setTemplate($template);
        $content = $viewRenderer->render($model);
        return $content;
    }

    /**
     * @param User $user
     */
    private function  updateUser(User $user)
    {
        $this->entityManager->merge($user);
        $this->entityManager->flush($user);
    }

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}

$notice = new ExpireNotice();
$notice->run();