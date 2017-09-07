<?php
namespace Api\V1\Rpc\Subscription;

use Api\V1\Controller\BaseActionController;
use Api\V1\Rest\Subscription\SubscriptionValidatorTrait;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Service\GiftCardService;
use Api\V1\Service\PlanService;
use Api\V1\Service\SubscriptionService;
use Api\V1\Service\UserService;
use Exception;
use Perpii\Message\EmailManager;
use Psr\Log\LoggerInterface;
use Zend\Validator\Date;
use Zend\View\Helper\ViewModel;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class SubscriptionController extends BaseActionController
{
    use SubscriptionValidatorTrait;

    /** @var null|Array $webConfig */
    private $webConfig = null;

    /** @var \Api\V1\Service\SubscriptionService */
    private $subscriptionService;

    /** @var  \Api\V1\Service\UserService */
    private $userService;

    /** @var  \Api\V1\Service\GiftCardService */
    private $giftCardService;

    /**
     * @var EmailManager
     */
    private $emailManager;


    public function __construct(
        $webConfig,
        SubscriptionService $subscriptionService,
        UserService $userService,
        GiftCardService $giftCardService,
        AuthenticationServiceInterface $authentication,
        AuthorizationInterface $authorization,
        EmailManager $emailManager,
        LoggerInterface $logger)
    {
        parent::__construct($authentication, $authorization, $logger);
        $this->webConfig = $webConfig;
        $this->subscriptionService = $subscriptionService;
        $this->userService = $userService;
        $this->giftCardService = $giftCardService;
        $this->emailManager = $emailManager;
    }


    /**
     * @return JsonModel|ApiProblemModel
     */
    public function validatePromoAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $promoCode = $router->getParam('promoCode');

            if (empty($promoCode)) {
                throw new Exception('Please provide promo code', 417);
            }

            $isFree = $router->getParam('isFree');
            $data = array('promoCode' => $promoCode, 'isFree' => $isFree);

            $coupon = $this->validateCoupon($data);

            if ($coupon instanceof ApiProblem) {
                return new ApiProblemModel($coupon);
            }

            //check for some plans on current release only
            if ($coupon->isFree() || !$coupon->getPlan() || PlanService::isSupportedPromoPlan($coupon->getPlan())) {
                return new JsonModel(array('status' => '200', 'message' => 'Promo code is valid'));
            } else {
                return new ApiProblemModel(new ApiProblem(403, 'Promotion code is not supported on this version'));
            }
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @return ApiProblemModel
     */
    public function validateRecipientEmailAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();

            //check data format
            $email = $router->getParam('email');
            if (empty($email)) {
                throw new \Exception('Please provide a valid email address', 417);
            }

            $deliveryDate = $router->getParam('delivery_date');
            if (empty($deliveryDate)) {
                throw new \Exception('Please enter a valid delivery date', 417);
            }

            if (intval($deliveryDate) <= 0) {
                throw new \Exception('Delivery date is invalid', 417);
            }

            //check expired delivery  date
            if ($deliveryDate) {
                $deliveryDate = (new \DateTime())->setTimestamp(intval($deliveryDate))->setTime(0, 0, 0);
                $now = (new \DateTime())->setTime(0, 0, 0);
                if ($now > $deliveryDate) {
                    throw new \Exception('Delivery date cannot be in the past', 417);
                }
            }

            //validate email
            $user = $this->userService->findByEmail($email);
            if ($user) {
                //check his subscription
                /** @var  \Api\V1\Entity\Subscription $subscription */
                $subscription = $this->subscriptionService->find($user->getSubscriptionId());
                if ($subscription) {
                    //check free account
                    if ($subscription->getExpireAt() == 0) {
                        throw new \Exception('User is exists and their subscription never expires', 417);
                    }
                    //check non expire accoun
                    if ($subscription->getExpireAt() > time()) {
                        throw new \Exception('This user exists and their subscription is valid', 417);
                    }
                }
            } else {
                //check to see if his previous has expired or not
                $gift = $this->giftCardService->findByRecipientEmail($email);

                //todo: review, strange why i must receive on gift a time only
                if ($gift) {
                    $expireTime = (new \DateTime())
                        ->setTimestamp($gift->getDeliveryDate())
                        ->add(\DateInterval::createFromDateString('1 year'))
                        ->getTimestamp();
                    if ($expireTime > time()) {
                        throw new \Exception('Previous gift subscription has not expired', 417);
                    }
                }
            }
            return new JsonModel(array('status' => '200', 'message' => 'Recipient email is valid'));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * List revenue by date range
     * @return JsonModel|ApiProblemModel
     */
    public function revenueReportAction()
    {
        try {
            //admin only
            if (!$this->isAdmin()) {
                return new ApiProblemModel(new ApiProblem(401, 'Unauthorized'));
            }

            $router = $this->getEvent()->getRouteMatch();
            //check data format
            $from = $router->getParam('from', null);
            $from = ($from !== null) ? $from : 0;
            if (!is_numeric($from)) {
                throw new \Exception('From date must be a unix time stamp', 417);
            }

            $to = $router->getParam('to', null);
            $to = ($to !== null) ? $to : 0;
            if (!is_numeric($to)) {
                throw new \Exception('To date must be a unix time stamp', 417);
            }
            $to = ($to != 0) ? $to : 999999999999999;

            $totalRevenue = 0;
            $totalRegister = 0;
            $results = $this->subscriptionService->getRevenue(intval($from), intval($to));
            foreach ($results as $plan) {
                $totalRevenue += $plan['revenue'];
                $totalRegister += $plan['total'];
            }
            return new JsonModel(array('totalRevenue' => $totalRevenue, 'totalRegister' => $totalRegister, 'details' => $results));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Send feedback information action
     *
     * @return void|\Exception
     */
    public function helpAction()
    {
        try {

            $request = $this->getRequest();
            $content = json_decode($request->getContent());
            if (!$content) {
                throw new \Exception('Cannot find content', 417);
            }

            if (!isset($content->firstName)) {
                throw new \Exception('First Name is required', 417);
            }
            $firstName = $content->firstName;

            if (!isset($content->lastName)) {
                throw new \Exception('Last Name is required', 417);
            }
            $lastName = $content->lastName;

            if (!isset($content->email)) {
                throw new \Exception('Email is required', 417);
            } else if (!filter_var($content->email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Email is not in a valid format', 417);
            }
            $email = $content->email;

            if (!isset($content->message)) {
                throw new \Exception('A message is required', 417);
            }
            $message = $content->message;
            $phone = isset($content->phone) ? $content->phone : ''; // because phone is not required

            $data = array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'message' => $message,
                'phone' => $phone
            );

            if (!isset($this->webConfig['infoEmailAddress'])) {
                throw new \Exception('API server is not configured with an "info" email address', 500);
            }

            $this->emailManager
                ->setSenderEmail($email)
                ->setSenderName($firstName . ' ' . $lastName)
                ->setReceiver($this->webConfig['infoEmailAddress'])
                ->setTemplate('/subscription/help.phtml')
                ->setTemplateData($data)
                ->send();
            return new JsonModel(array(
                    'status' => '200',
                    'message' => 'Message sent successfully')
            );

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @throws \Exception
     * @return string
     */
    function getResourceId()
    {
        return AclAuthorization::RESOURCE_COUPON;
    }
    /**
		* * developer - raviteja
		* *
		* * @return boolean
		* * @throws \Api\V1\Service\Extension\BusinessException
		* */
	public function checkuserAction()
	{
		try {
			$router = $this->getEvent()->getRouteMatch();
			$subscriptionId = trim($router->getParam('subscription_id'));
			$stmnt2 = $this->subscriptionService->checkUser($subscriptionId);
			
			if($stmnt2[0]['user_id']) {
				$uid = $stmnt2[0]['user_id'];
				//return new JsonModel(array('status' => '200', 'message' => "{'user_id:$uid,'subscription_id':$subscriptionId}"));
				return new JsonModel(array('status' => '200', 'user_id'=>$uid,'subscription_id'=>$subscriptionId));
			} else {
				return new JsonModel(array('status' => '200', 'user_id'=>'','subscription_id'=>$subscriptionId));
			}
		} catch (\Exception $ex) {
			return $this->processUnhandledException($ex);
		}
	}
}
