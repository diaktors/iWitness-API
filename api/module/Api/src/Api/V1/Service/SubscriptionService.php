<?php
namespace Api\V1\Service;

use Api\V1\Entity\Coupon;
use Api\V1\Entity\GiftCard;
use Api\V1\Entity\Plan;
use Api\V1\Entity\Sender;
use Api\V1\Entity\Subscription;
use Api\V1\Entity\User;
use Api\V1\Repository\PlanRepository;
use Api\V1\Repository\SenderRepository;
use Api\V1\Service\Payment\AppleInAppService;
use Api\V1\Service\Payment\GoogleInAppService;
use Api\V1\Service\Payment\PaymentAbstract;
use Api\V1\Service\PlanService;
use DateInterval;
use Doctrine\ORM\EntityManager;
use Perpii\Util\String;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class SubscriptionService extends ServiceAbstract
{

    const ENTITY_CLASS = 'Api\V1\Entity\Subscription';

    /** @var array */
    private $subscriptionConfig = null;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $subscriptionRepository = null;

    /** @var \Doctrine\ORM\EntityRepository */
    private $userRepository = null;

    /** @var \Doctrine\ORM\EntityRepository */
    private $couponRepository = null;

    /** @var SenderRepository */
    private $senderRepository = null;

    /** @var PlanRepository */
    private $planRepository = null;

    /**
     * @var Payment\PaymentAbstract|null
     */
    private $paymentService = null;

    /** @var \Api\V1\Service\Payment\GoogleInAppService */
    private $googleInAppService = null;

    /** @var \Api\V1\Service\Payment\AppleInAppService */
    private $appleInAppService = null;


    /**
     * @param array $config
     * @param Payment\PaymentAbstract $paymentAbstract
     * @param Payment\GoogleInAppService $googleInAppService
     * @param Payment\AppleInAppService $appleInAppService
     * @param EntityManager $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        array $config,
        PaymentAbstract $paymentAbstract,
        GoogleInAppService $googleInAppService,
        AppleInAppService $appleInAppService,
        EntityManager $entityManager,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->paymentService = $paymentAbstract;
        $this->googleInAppService = $googleInAppService;
        $this->appleInAppService = $appleInAppService;

        $this->subscriptionRepository = $entityManager->getRepository('Api\V1\Entity\Subscription');
        $this->userRepository = $entityManager->getRepository('Api\V1\Entity\User');
        $this->couponRepository = $entityManager->getRepository('Api\V1\Entity\Coupon');
        $this->planRepository = $entityManager->getRepository('Api\V1\Entity\Plan');
        $this->senderRepository = $entityManager->getRepository('Api\V1\Entity\Sender');

        $this->subscriptionConfig = $config;

    }

    /**
     * @param $params
     * @return Coupon
     */
    public function getCoupon($params)
    {
        return $this->couponRepository->findOneBy($params);
    }

    /**
     * @param array $data
     * @param Coupon $coupon
     * @return Subscription
     */
    public function createFreeSubscription(array $data, Coupon $coupon)
    {
        $subscriptionId = UUID::generate();
        /** @var \Api\V1\Entity\Subscription $subscription */
        $subscription = new Subscription($subscriptionId);
        $subscription->setCoupon($coupon);

        $this->updateSubscriptionPhoneModelIp($subscription, $data);

        $subscription
            ->setPlan('free')
            ->setStartAt(time())
            ->setExpireAt(0)
            ->setAmount(0);

        if ($coupon) {
            $this->entityManager->merge($coupon);
            $coupon->setCurrentUsages($coupon->getCurrentUsages() + 1);
        }

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    /**
     * @param array $data
     * @return bool|ApiProblem
     */
    public function verifyInAppPurchase(array $data)
    {
        switch ($data['packageName']) {
            case PlanService::IOS_MONTHLY_SUBSCRIPTION:
            case PlanService::IOS_MONTHLY_SUBSCRIPTION_AR:
            case PlanService::IOS_YEARLY_SUBSCRIPTION:
            case PlanService::IOS_YEARLY_SUBSCRIPTION_AR:
                return $this->appleInAppService->verifyInAppPurchase($data);

            case PlanService::ANDROID_MONTHLY_SUBSCRIPTION:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTION:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST1:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST1:
                return $this->googleInAppService->verifyInAppPurchase($data);

            default:
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['message' => 'The packageName ' . $data['packageName'] . ' is invalid'],
                ));
        }
    }

    /**
     * @param $data
     * @param Coupon $coupon
     * @return Subscription
     */
    public function createInAppPurchaseSubscription($data, Coupon $coupon = null)
    {
        $this->verifyReceiptId($data);

        switch ($data['packageName']) {
            case PlanService::IOS_MONTHLY_SUBSCRIPTION:
            case PlanService::IOS_MONTHLY_SUBSCRIPTION_AR:
            case PlanService::IOS_YEARLY_SUBSCRIPTION:
            case PlanService::IOS_YEARLY_SUBSCRIPTION_AR:
				$this->verifyOriginalReceiptId($data);
                $data['payment_gateway'] = $this->appleInAppService->getName();
                break;

            case PlanService::ANDROID_MONTHLY_SUBSCRIPTION:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTION:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST1:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST1:
                $data['payment_gateway'] = $this->googleInAppService->getName();
                break;

        }

        $plan = PlanService::inAppPackageToPlan($data['packageName']);
        $data['plan'] = $plan;

        return $this->createInAppSubscription($data, $coupon);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function verifyReceiptId(array $data)
    {
        if (!isset($data['receiptId'])) {
            throw new \Exception('receiptId could not be null');
        }

        $subscription = $this->subscriptionRepository->findOneBy(array('receiptId' => $data['receiptId']));
        if ($subscription && $subscription->getUser()) {
            throw new \Exception('Account Already Exists with this Subscription.', 422);
		}
    }
    /**
     * @param array $data
     * @throws \Exception
     */
    protected function verifyOriginalReceiptId(array $data)
    {
        if (isset($data['originalreceiptid'])) {
            //throw new \Exception('receiptId could not be null');
			if (!isset($data['userId']) && !isset($data['isRenew'])) {
				$subscription = $this->subscriptionRepository->findOneBy(array('originalreceiptid' => $data['originalreceiptid']));
				if ($subscription && $subscription->getUser()) {
					throw new \Exception('Account Already Exists with this Subscription.', 422);
				}	
			} else {
				$user = $this->userRepository->findOneBy(array('phone' => $data['userId']));
				if ($user) {
					$subscription = $this->subscriptionRepository->findOneBy(array('id' => $user->getSubscriptionId()));
					if($subscription) {
						$this->updateSubscriptionOreceiptId($subscription,$data, $user);
					}
				}
			}
		}
    }

    /**
     * @param array $data
     * @param Coupon $coupon
     * @return Subscription
     */
    protected function createInAppSubscription(array $data, Coupon $coupon = null)
    {
        $timestamp = isset($data['receiptDate']) ? (int)$data['receiptDate'] : time();

        $plan = $this->planRepository->findPlanByKey($data['plan']);

		$data['quantity'] = isset($data['recipients']) ? count($data['recipients']) : 1;

        if (isset($data['week_test'])){
        	$expireAt = (new \DateTime())
            	->setTimestamp($timestamp)
            	->add(\DateInterval::createFromDateString('1 day'))
            	->setTime(23, 59, 59); //end of day
		    $expireAt = $expireAt->getTimestamp();
		}else if ($data['packageName'] =="com.iwitness.monthly_subscribe_ar_new"){
        	$expireAt = $timestamp+300;
		
		}else if ($data['packageName'] == "com.iwitness.yearly_subscribe_ar_new"){
            $expireAt = $timestamp+3600; 
		}else{		
        $expireAt = (new \DateTime())
            ->setTimestamp($timestamp)
            ->add(\DateInterval::createFromDateString($plan->getLength() . ' months'))
			->setTime(23, 59, 59); //end of day
		$expireAt = $expireAt->getTimestamp();
		}
		
        $subscriptionId = UUID::generate();
        /** @var \Api\V1\Entity\Subscription $subscription */
        $subscription = new Subscription($subscriptionId);
        $subscription->setCoupon($coupon);
		$this->updateSubscriptionPhoneModelIp($subscription, $data);
		$receipt_data ='';
		if(isset($data['receiptData']))
		{
			$receipt_data = $data['receiptData'];
		}
		$originalReceiptId="";
		if(isset($data['originalreceiptid']))
		{
			$originalReceiptId = $data['originalreceiptid'];
		}
		
		
	    error_log(print_r($data, TRUE),3, '/volumes/log/api/test-log.log');

        $subscription
            ->setOriginalPhone($data['originalPhone'] ?: null)
            ->setPlan($data['plan'])
            ->setReceiptId($data['receiptId'])
            ->setOriginalReceiptId($originalReceiptId)
            ->setReceiptData($receipt_data)
            ->setPurchasedToken($data['purchasedToken'])
            ->setProductId($data['productId'])
            ->setIsActive(true)
            ->setStartAt($timestamp)
            ->setExpireAt($expireAt)
            ->setAmount($plan->getPrice());

        if (isset($data['payment_gateway'])) {
            $subscription->setPaymentGateway($data['payment_gateway']);
        }

        if ($coupon) {
            $this->entityManager->merge($coupon);
            $coupon->setCurrentUsages($coupon->getCurrentUsages() + 1);
        }

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    /**
     * @param array $data
     * @param Coupon $coupon
     * @throws \Exception
     * @return Subscription
     */
    public function createPaidSubscription(array $data, Coupon $coupon = null)
    {
        if ($coupon) {
            $data['promoCode'] = $coupon->getCode();
        }
        $data = $this->preparePaymentData($data, $coupon);
        //create Payment Billing
        $paymentResult = $this->paymentService->createBilling($data);

        if (!$paymentResult->status) {
            throw new \Exception($paymentResult->message, 422);
        }

        /** @var \Api\V1\Entity\Subscription $subscription */
        $subscription = new Subscription($data['subscriptionId']);
        $subscription->setCoupon($coupon);
        $subscription->setReceiptId($paymentResult->billingId);

        $this->updateSubscriptionPhoneModelIp($subscription, $data);

        $subscription
            ->setPlan($data['plan'])
            ->setStartAt(time())
            ->setExpireAt($data['expire']->getTimestamp())
            ->setAmount($data['amount'])
            ->setReceiptId($paymentResult->billingId)
            ->setPaymentGateway($this->paymentService->getName());

        //increase coupon using if any
        if ($coupon) {
            $this->entityManager->merge($coupon);
            $coupon->setCurrentUsages($coupon->getCurrentUsages() + 1);
        }

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
        //create Automated Recurring Billing
        $paymentResult = $this->paymentService->createARBBilling($data);
        if ($paymentResult->status) {
            $subscription->setArbBillingId($paymentResult->billingId);
            $this->entityManager->flush($subscription);
        }

        return $subscription;
    }


    /**
     * @param array $data
     * @param \Api\V1\Entity\User $user
     * @throws \Exception
     * @return Subscription
     */
    public function buyGiftCards(array $data, User $user = null)
    {
        if ($user) {
            $data['customerId'] = $user->getId();
		}
		$data = $this->preparePaymentData($data);
		$data['amount'] = count($data['recipients']) * $data['amount'];
		//error_log("Buygiftcard.......", 3, "/volumes/log/api/test-log.log");
		if ($data['plan'] != PlanService::FREE_GIFT_CARD_PLAN){

			//create Payment Billing
			$paymentResult = $this->paymentService->createBilling($data);
			//die();
			//error_log(print_r($paymentResult, TRUE), 3, "/volumes/log/api/test-log.log");
			if (!$paymentResult->status) {
				throw new \Exception($paymentResult->message, 422);
			}

			/** @var \Api\V1\Entity\Subscription $subscription */
			$subscription = new Subscription($data['subscriptionId']);
			$this->updateSubscriptionPhoneModelIp($subscription, $data);

			////cannot use this subscription directly
			$subscription
				->setPlan($data['plan'])
				->setStartAt(time())
				->setExpireAt($data['expire']->getTimestamp())
				->setAmount($data['amount'])
				->setReceiptId($paymentResult->billingId)
				->setPaymentGateway($this->paymentService->getName());
		}
		else{
			$subscription = new Subscription($data['subscriptionId']);
			$this->updateSubscriptionPhoneModelIp($subscription, $data);
			$subscription
				->setPlan('freegiftcard')
				->setStartAt(time())
				->setExpireAt($data['expire']->getTimestamp())
				->setAmount(0);

		}

        $sender = $this->senderRepository->insertOrUpdate($data['senderName'], $data['senderEmail']);

        //update coupon service
        $this->createGiftCards($data, $sender);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    /**
     * @param array $data
     * @param \Api\V1\Entity\Sender|\Api\V1\Entity\User $sender
     * @throws \Exception
     */
    public function  createGiftCards(array $data, Sender $sender = null)
    {
        if (!isset($data['recipients'])) {
            throw new \Exception('Recipient  does not exist');
        }

        $recipients = $data['recipients'];
		if ($data['plan'] != PlanService::FREE_GIFT_CARD_PLAN){
        	$plan = $this->planRepository->findPlanByKey(PlanService::YEAR_GIFT_CARD_PLAN);
		}
	    //error_log(print_r($data, TRUE),3, '/volumes/log/api/test-log.log');
        foreach ($recipients as $recipient) {
            $giftId = UUID::generate();
			$gift = new GiftCard($giftId);
            $gift
                ->setRecipientEmail($recipient['email'])
                ->setMessage($recipient['message'])
                ->setDeliveryDate(intval($recipient['deliveryDate']))
                ->setCode(String::generateRandomNumber(14))
                ->setIsActive(true)
                ->setMaxRedemption(1)
                ->setName($recipient['name'])
                ->setCurrentUsages(0)
                ->setSubscriptionId($data['subscriptionId']);
			if ($data['plan'] != PlanService::FREE_GIFT_CARD_PLAN){
				$gift
                	->setPlan($plan->getName())
                	->setPrice($plan->getPrice())
                	->setSubscriptionLength($plan->getLength());
			}else{
				$gift
                	->setPlan('freegiftcard')
                	->setPrice(0)
                	->setSubscriptionLength(1);
			}	
            if ($sender) {
                $gift->setSenderId($sender->getId());
            }
            $this->entityManager->persist($gift);
        }
    }

    /**
     * @param array $data
     * @param Coupon $coupon
     * @throws \Api\V1\Repository\Exception
     * @throws \Exception
     * @return array
     */
    private function preparePaymentData(array $data, Coupon $coupon = null)
    {
        //prepare data before calling payment service
        $subscriptionId = UUID::generate();
        $data['subscriptionId'] = $subscriptionId;

		//set expired date
		if ($data['plan'] != PlanService::FREE_GIFT_CARD_PLAN){ 
			$data['expDate'] = str_pad($data['expMonth'], 2, '0', STR_PAD_LEFT) . '-' . $data['expYear'];
			$planservice = $data['plan'];
		}
		else{
			$planservice = PlanService::FREE_PLAN;
		}
        //address
        $address2 = isset($data['address2']) ? $data['address2'] : '';
		$data['address'] = rtrim($data['address1'] . ',' . $address2, ', ');

        
        //calculate amount
        if (isset($data['plan']) && $data['plan'] != PlanService::ANY_PLAN) {
            $plan = $this->planRepository->findPlanByKey($planservice);
        } elseif ($coupon) {
            $plan = new Plan(UUID::generate(), PlanService::ANY_PLAN);
            $plan->setPrice($coupon->getPrice());
            $plan->setLength($coupon->getSubscriptionLength());
        } else {
            throw new \Exception('Plan could not be null');
        }

		if ($data['existingUser'] =='yes')
			$amount = '19.95';
		else
        	$amount = $plan->getPrice(); // PriceSetting::planToAmount($data['plan']);
        $data['amount'] = $amount;

        //month
        $months = $plan->getLength(); // PriceSetting::planToMonths($data['plan']);
        $data['months'] = $months;

        $data['description'] = $plan->getDescription(); // PriceSetting::planToDescription($data['plan']);

		// when will subscription expire?
        $expire = new \DateTime();
        if (isset($data['week_test'])){
              $expire
                  ->add(\DateInterval::createFromDateString("1 day"))
				  ->setTime(23, 59, 59);
		}else{
        $expire
            ->add(\DateInterval::createFromDateString("$months months"))
            ->setTime(23, 59, 59);
        }
        $data['expire'] = $expire;

        return $data;
    }


    /**
     * @param Subscription $subscription
     * @param \Api\V1\Entity\User $user
     * @internal param array $data
     * @return Subscription
     */
    public function updateSubscriptionCreator(Subscription &$subscription, User $user = null)
    {
        //get user if any
        if ($user) {
            $subscription->setUser($user);
            $this->entityManager->merge($subscription);
            $this->entityManager->flush();
        }
        return $subscription;
    }

    /**
     * update subscription purchased token
     * @param Subscription $subscription
     * @param $data
     * @internal param $purchasedToken
     * @return Subscription
     */
    public function updateSubscriptionBilling(Subscription &$subscription, $data)
    {
        $subscription->setProductId($data['productId']);
        $subscription->setPurchasedToken($data['purchasedToken']);

        $this->entityManager->merge($subscription);
        $this->entityManager->flush();
        return $subscription;
    }


    /**
     * @param $arbBillingId
     * @return Subscription | null
     */
    public function findByArb($arbBillingId)
    {
        return $this->getRepository()->findOneBy(array('arbBillingId' => $arbBillingId));
    }

    /**
     * @param $receiptId
     * @return Subscription | null
     */
    public function findReceiptId($receiptId)
    {
        return $this->getRepository()->findOneBy(array('receiptId' => $receiptId));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getByExpiredUsers($paymentGateway='')
    {
        return $this->getRepository()->getByExpiredUsers($paymentGateway);
    }

    /**
     * Revenue report
     * @param $from
     * @param $to
     * @return array
     */
    public function getRevenue($from, $to)
    {
        return $this->getRepository()->getRevenue($from, $to);
    }


    /**
     * @param Subscription $subscription
     * @param array $data
     * @return Subscription
     */
    private function updateSubscriptionPhoneModelIp(Subscription &$subscription, array $data)
    {
        if (isset($data['originalPhone'])) $subscription->setOriginalPhone($data['originalPhone']);
        if (isset($data['originalPhoneModel'])) $subscription->setOriginalPhoneModel($data['originalPhoneModel']);
        if (isset($data['customerIp'])) $subscription->setCustomerIp($data['customerIp']);
        return $subscription;
    }
    /**
     * @param Subscription $subscription
     * @param array $data
     * @return Subscription
     */
    private function updateSubscriptionOreceiptId(Subscription &$subscription, array $data, $user)
    {
        if (isset($data['originalreceiptid'])) $subscription->setOriginalReceiptId($data['originalreceiptid']);
        if (isset($data['receiptId'])) $subscription->setReceiptId($data['receiptId']);
        if (isset($data['receiptData'])) $subscription->setReceiptData($data['receiptData']);
        $timestamp = isset($data['receiptDate']) ? (int)$data['receiptDate'] : time();
		$expireAt = time()+300;
		$subscription->setExpireAt($expireAt);
        return $subscription;
    }
    public function checkUser($subscriptionId) {
		$sql = "SELECT uuid_to_str(user_id) as user_id from subscription where uuid_to_str(id)='$subscriptionId'";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
	}
}
