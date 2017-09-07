#!/usr/bin/php
<?php

require dirname(__FILE__) . '/../Bootstrap.php';

use Api\V1\Service\SettingService;
use Api\V1\Service\SubscriptionService;
use Api\V1\Service\UserService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Zend\Json;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\Client;
use ZF\ApiProblem\ApiProblem;

class AppleSubscriptionStatusUpdater implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var  ServiceManager;
     */
    private $serviceManager = null;

    /** @var \AuthorizeNetTD */
    private $authorizeNetTD = null;

    /** @var array */
    private $config = null;

    /** @var UserService */
    private $userService = null;

    /** @var SubscriptionService */
    private $subscriptionService = null;

    /** @var SettingService */
    private $settingService = null;

    /** @var EntityManager */
    private $entityManager = null;



    public function __construct()
    {
        Bootstrap::initializeLogger('logger-subscription');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));

        //configuration
        $config = $this->serviceManager->get('config');
        $this->config = $config['paymentGateWays']['appleStore'];

        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');

        $this->userService = $this->serviceManager->get('Api\V1\Service\UserService');
        $this->subscriptionService = $this->serviceManager->get('Api\V1\Service\SubscriptionService');
        $this->settingService = $this->serviceManager->get('Api\\V1\\Service\\SettingService');

    }


    /**
     * Execute Automated Recurring Billing (ARB) updating
     * The Apple Billing Subscription can be query by:
     * - packageName: 'com.iwitness.android'
     * - productId: 'monthly_sub_2.99', 'yearly_sub_29.99'
     * - purchasedToken: { from purchased data }
     * Refer to:
     * https://developers.google.com/android-publisher/api-ref/purchases/subscriptions/get
     */
    public function  run()
    {
        try {
			//Construct AndroidPublisher before authenticate
            $this->debug("*** Begin of process apple subscription status");
			$subscriptions = $this->subscriptionService->getByExpiredUsers('Apple');
			$this->debug('Found ' . count($subscriptions) . ' apple subscriptions');
			foreach ($subscriptions as $subscription) {
				if ($subscription->getReceiptData())
		              $this->processSubscription($subscription);
            }

            //4. update last run to database
            $this->debug('*** End of process apple subscription status ');
        } catch (\Exception $ex) {
            $this->debug($ex->getMessage());
        }
    }

    /**
     * @param \Api\V1\Entity\Subscription $subscription
     * @param Google_Service_AndroidPublisher $publisher
     */
    private function processSubscription(\Api\V1\Entity\Subscription $subscription)
    {
        if ($subscription->getReceiptData()) {
			//error_log("\n ". $subscription->getId(), 3, "/volumes/log/api/test-log.log");
			$this->debug('Begin update apple Subscription ' . $subscription->getId());
			//$this->debug('Receipt Data: ' . $subscription->getReceiptData());
			$postData = json_encode(
				array('receipt-data' => $subscription->getReceiptData(), 'password' =>  $this->config['sharedSecret'])
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_URL, $this->config['verifyReceiptUrl']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

			$response = curl_exec($ch);
			$errNo = curl_errno($ch);
			$errMsg = curl_error($ch);
			curl_close($ch);

			if ($errNo != 0) {
				return new ApiProblem(422, 'Failed Validation', null, null, array(
						'validation_messages' => $errMsg,
					));
			}

			$inappSubscription = json_decode($response);
			//error_log(print_r($inappSubscription->, TRUE), 3, "/volumes/log/api/test-log.log");
			if ($inappSubscription && $inappSubscription->status ==0)  {
				//if ($inappSubscription->autoRenewing !=''){
					//$expiredDate = $inappSubscription->getValidUntilTimestampMsec() / 1000;
					$expiredDate = $inappSubscription->receipt->latest_receipt_info[len(latest_receipt_info)-1]->expires_date_ms / 1000;
					$initialDate = $inappSubscription->receipt->latest_receipt_info[0]->purchase_date_ms / 1000;
					$receiptData = $inappSubscription->latest_receipt;
                	//$initialDate = $inappSubscription->getInitiationTimestampMsec() / 1000;
                	$current = time();
			//error_log("\n cur time: ". $current, 3, "/volumes/log/api/test-log.log");
			//error_log("\n expire time: ". $expiredDate, 3, "/volumes/log/api/test-log.log");
                	if ($expiredDate > $current) {
                    	$this->debug('Found Apple Billing Subscription: ');

                    	$this->updateSubscriptionDuration($subscription, $initialDate, $expiredDate, $receiptData);

                    	/** @var $user \Api\V1\Entity\User */
                    	$user = $subscription->getUser();
			            error_log(print_r($user->getId(), TRUE), 3, "/volumes/log/api/test-log.log");

                    	if ($user) {
                        	$this->userService->updateUserSubscription($user, $subscription);
                    	}
					}
				//}
            } else {
                $this->debug('The Apple Billing Subscription has been expired.');
            }
        }
    }

    /**
     * @param \Api\V1\Entity\Subscription $subscription
     * @param $startAt
     * @param $expireAt
     */
    public function updateSubscriptionDuration(\Api\V1\Entity\Subscription &$subscription, $startAt, $expireAt, $receiptData)
    {
        $subscription
            ->setStartAt($startAt)
            ->setExpireAt($expireAt)
            ->setReceiptData($receiptData);

        $this->entityManager->merge($subscription);
        $this->entityManager->flush();
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

}

$subscriptionStatus = new AppleSubscriptionStatusUpdater();
$subscriptionStatus->run();
