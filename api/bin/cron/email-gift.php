#!/usr/bin/php
<?php
require dirname(__FILE__) . '/../Bootstrap.php';

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class EmailGift implements LoggerAwareInterface
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

    /** @var \Api\V1\Service\SubscriptionService */
    private $subscriptionService;

    public function __construct()
    {
        Bootstrap::initializeLogger('logger-email');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));
        $this->giftCardService = $this->serviceManager->get('Api\V1\Service\GiftCardService');
        $this->senderService = $this->serviceManager->get('Api\V1\Service\SenderService');
        $this->subscriptionService = $this->serviceManager->get('Api\V1\Service\SubscriptionService');
        $this->emailManager = $this->serviceManager->get('Perpii\\Message\\EmailManager');
    }

    /**
     * Execute email sending
     */
    public function  run()
    {
        try {
            $this->debug('Begin to process gift card email ');
            $giftCards = $this->giftCardService->findTodayDeliverGift();
            if ($giftCards && count($giftCards) > 0) {
                $this->debug('Found '.count($giftCards).' giftcards');
                foreach ($giftCards as $gift) {
                    /** @var  \Api\V1\Entity\GiftCard $gift */
                    $this->sendGiftEmail($gift);
                    $this->giftCardService->setDelivered($gift);
                    $this->debug('One email has sent to: ' . $gift->getRecipientEmail());
                }
            }else{
                $this->debug('No gift was found ');
            }
            $this->debug('End of process gift card email ');
        } catch (\Exception $ex) {
            $this->error(print_r($ex, true));
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
			$subscription = $this->subscriptionService->find($gift->getSubscriptionId());
			if (!$subscription){
                $this->debug('Couldn\'t find subscription of sender id' . $gift->getSenderId());
                return false;
		    }

            if (!$sender) {
                $this->debug('Couldn\'t find sender id ' . $gift->getSenderId());
                return false;
			}
			$startedAt = $subscription->getStartAt();
			$start_Date = date('m-d-Y H:i:s', $startedAt);
			$gift_plan = $gift->getPlan();
            $vars = array(
                'giftCard' => $gift,
                'sender' => $sender,
                'startdate' => $start_Date,
                'tagline' => 'DON\'T DELETE THIS MESSAGE! You\'ve received an iWitness E-Gift! You\'ll need the
                              <span style="text-decoration: underline;">claim code</span> to activate your account.'
            );

			if ($gift_plan =='freegiftcard')
				$email_template = '/gift/month-email-gift.phtml';
			else
				$email_template = '/gift/email-gift.phtml';

            $this->debug('Gift plan: ' . $gift_plan);
            $this->debug('Gift template: ' . $email_template);

            $this
                ->emailManager
                ->setReceiver($gift->getRecipientEmail())
                ->setReplyTo($sender->getEmail(), $sender->getFullName())
                ->setTemplate($email_template)
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

$emailGift = new EmailGift();
$emailGift->run();
