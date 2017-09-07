#!/usr/bin/php
<?php

require dirname(__FILE__) . '/../Bootstrap.php';

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class TestEmail implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var  \Zend\ServiceManager\ServiceManager;
     */
    private $serviceManager = null;

    /** @var  \Api\V1\Service\GiftCardService */
    private $giftCardService;

    /** @var  \Perpii\Message\EmailManager */
    private $emailManager;

    /** @var \Api\V1\Service\SenderService */
    private $senderService;

    /** @var \Api\V1\Service\UserService */
    private $userService;


    public function __construct()
    {
        Bootstrap::initializeLogger('logger-email');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));
        $this->senderService = $this->serviceManager->get('Api\V1\Service\SenderService');
        $this->emailManager = $this->serviceManager->get('Perpii\\Message\\EmailManager');
        $this->userService = $this->serviceManager->get('Api\V1\Service\UserService');
    }

    /**
     * Execute email sending
     */
    public function  run()
    {
        try {
            $user = $this->userService->getUserById('b21fc9fa-270c-11e4-835e-12c7259d87ad');
            $vars = array(
                'to' => $user
            );

            $this
                ->emailManager
                ->setReceiver('jared@webonyx.com')
                //->setSenderEmail('info@iwitness.com')
               
                ->setTemplate('/user/welcome-email.phtml')
                ->setTemplateData($vars)
                ->send();

        } catch (\Exception $ex) {
			print_r($ex);
			//$this->error(print_r($ex, true));
        }
    }

    /**
     * @param Api\V1\Entity\GiftCard $gift
     * @return bool
     * @internal param $subscription
     */
    private function sendGiftEmail(\Api\V1\Entity\GiftCard $gift)
    {
        try {
            /** @var \Api\V1\Entity\Sender $sender */
            $sender = $this->senderService->find($gift->getSenderId());

            if (!$sender) {
                $this->debug('Couldn\' find sender id ' . $gift->getSenderId());
                return false;
            }

            $vars = array(
                'giftCard' => $gift,
                'sender' => $sender,
                'tagline' => 'DON\'T DELETE THIS MESSAGE! You\'ve received an iWitness E-Gift! You\'ll need the
                              <span style="text-decoration: underline;">claim code</span> to activate your account.'
            );

            $this
                ->emailManager
                ->setReceiver($gift->getRecipientEmail())
                ->setSenderEmail($sender->getEmail())
                ->setSenderName($sender->getFullName())
                ->setTemplate('/gift/email-gift.phtml')
                ->setTemplateData($vars)
                ->send();

        } catch (\Exception $ex) {
            $this->error('Couldn\'t send email to ' . $gift->getRecipientEmail(), ['exception' => $ex]);
        }

    }

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}

//$test = new TestEmail();
//$test->run();
