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

class GoogleSubscriptionStatusUpdater implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

   // const IWITNESS_APP_NAME = 'com.iwitness.android';
    const IWITNESS_APP_NAME = 'com.iwitness.androidapp';
    //const IWITNESS_APP_NAME = 'com.iwitness.androidtest';
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

    /** @var Google_Client */
    private $googleClient = null;


    public function __construct()
    {
        Bootstrap::initializeLogger('logger-subscription');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));

        //configuration
        $config = $this->serviceManager->get('config');
        $this->config = $config['paymentGateWays']['googlePlay'][self::IWITNESS_APP_NAME];

        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');

        $this->userService = $this->serviceManager->get('Api\V1\Service\UserService');
        $this->subscriptionService = $this->serviceManager->get('Api\V1\Service\SubscriptionService');
        $this->settingService = $this->serviceManager->get('Api\\V1\\Service\\SettingService');

        $this->googleClient = $this->getGoogleApiClient();
    }

    /**
     * Init Google Api Client
     * @return \Google_Client
     */
    public function getGoogleApiClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('iWitness');
        $client->setAccessType('offline');
        $client->setClientId($this->config['clientID']);
        $client->setClientSecret($this->config['clientSecret']);
        $client->setDeveloperKey($this->config['developerKey']);
        $client->setRedirectUri($this->config['redirectUri']);
        $client->setScopes($this->config['scopes']);
        $client->refreshToken($this->config['refreshToken']);
        //$client->refreshToken('4/rJdxEadTezYBpt1Vm2Cfsg5qA4-ZzHUt4ac37tj7yYQ');
        return $client;
    }

    public function authenticateGoogleApi()
	{
		//$key = file_get_contents('/var/www/api/vendor/google/apiclient/examples/key.p12');
        $cred = new Google_Auth_AssertionCredentials(
            $this->config['devAccountName'],
            $this->config['scopes'],
			$this->googleClient->getAccessToken()
			//$key
		);
        $this->googleClient->setAssertionCredentials($cred);
        $authenticate = $this->googleClient->getAuth();
        if ($authenticate->isAccessTokenExpired()) {
            $authenticate->refreshTokenWithAssertion($cred);
        }
    }

    /**
     * Execute Automated Recurring Billing (ARB) updating
     * The Google Billing Subscription can be query by:
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
            $publisher = new Google_Service_AndroidPublisher($this->googleClient);
            $this->authenticateGoogleApi();

            $this->debug("*** Begin of process subscription status");
			$subscriptions = $this->subscriptionService->getByExpiredUsers('Google');
			$this->debug('Found ' . count($subscriptions) . ' subscriptions');
		   // error_log("Subscription details ".count($subscriptions)." found  ...... \n" , 3, "/volumes/log/api/test-log.log");
			foreach ($subscriptions as $subscription) {
				if ($subscription->getPurchasedToken() &&  $subscription->getProductId())
		              $this->processSubscription($subscription, $publisher);
            }

            //4. update last run to database
            $this->debug('*** End of process subscription status ');
        } catch (\Exception $ex) {
            $this->debug($ex->getMessage());
        }
    }

    /**
     * @param \Api\V1\Entity\Subscription $subscription
     * @param Google_Service_AndroidPublisher $publisher
     */
    private function processSubscription(\Api\V1\Entity\Subscription $subscription, $publisher)
    {
        if ($subscription->getPurchasedToken() && $subscription->getProductId()) {
			$this->debug('Begin update Subscription ' . $subscription->getId());
			//error_log("Product Id : ".$subscription->getProductId()."\n" , 3, "/volumes/log/api/test-log.log");
			//error_log("Receipt Id : ".$subscription->getReceiptId()."\n" , 3, "/volumes/log/api/test-log.log");
            $inappSubscription = $publisher->purchases_subscriptions->get(
                self::IWITNESS_APP_NAME,
                $subscription->getProductId(),
                $subscription->getPurchasedToken()
			);
			//error_log("In appsubscription details: ".print_r($inappSubscription, TRUE), 3, "/volumes/log/api/test-log.log");
			if ($inappSubscription) {
				//if ($inappSubscription->autoRenewing !=''){
					//$expiredDate = $inappSubscription->getValidUntilTimestampMsec() / 1000;
					$expiredDate = $inappSubscription->getExpiryTimeMillis() / 1000;
                	$initialDate = $inappSubscription->getStartTimeMillis() / 1000;
                	//$initialDate = $inappSubscription->getInitiationTimestampMsec() / 1000;
                	$current = time();
                	if ($expiredDate > $current) {
						$this->debug('Found Google Billing Subscription \n ');
						//error_log("Found Google billing subscription \n", 3, "/volumes/log/api/test-log.log");

                    	$this->updateSubscriptionDuration($subscription, $initialDate, $expiredDate);

                    	/** @var $user \Api\V1\Entity\User */
                    	$user = $subscription->getUser();

                    	if ($user) {
                        	$this->userService->updateUserSubscription($user, $subscription);
                    	}
					}
				//}
            } else {
                $this->debug('The Google Billing Subscription has been expired.');
            }
        }
    }

    /**
     * @param \Api\V1\Entity\Subscription $subscription
     * @param $startAt
     * @param $expireAt
     */
    public function updateSubscriptionDuration(\Api\V1\Entity\Subscription &$subscription, $startAt, $expireAt)
    {
		//error_log("Called update subscription..... ", 3, "/volumes/log/api/test-log.log");
        $subscription
            ->setStartAt($startAt)
            ->setExpireAt($expireAt);

        $this->entityManager->merge($subscription);
		$this->entityManager->flush();
		//error_log("Subscription updated successfully....", 3, "/volumes/log/api/test-log.log");
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

$subscriptionStatus = new GoogleSubscriptionStatusUpdater();
$subscriptionStatus->run();
