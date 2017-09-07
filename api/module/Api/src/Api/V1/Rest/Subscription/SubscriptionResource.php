<?php
namespace Api\V1\Rest\Subscription;

use Api\V1\Entity\Coupon;
use Api\V1\Entity\Subscription;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\Payment\AppleInAppService;
use Api\V1\Service\Payment\GoogleInAppService;
use Api\V1\Service\PlanService;
use Api\V1\Service\SubscriptionService;
use Api\V1\Service\UserService;
use Doctrine\ORM\EntityNotFoundException;
use Herrera\Phar\Update\Exception\Exception;
use Perpii\InputFilter\InputFilterTrait;
use Api\V1\Resource\ResourceAbstract;
use Zend\Stdlib\DateTime;
use ZF\ApiProblem\ApiProblem;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class SubscriptionResource extends ResourceAbstract
{
    use SubscriptionValidatorTrait;

    /**
     * @var \Api\V1\Service\SubscriptionService
     */
    private $subscriptionService;

    /** @var \Api\V1\Service\UserService */
    private $userService;

    /**
     * @param SubscriptionService $subscriptionService
     * @param UserService $userService
     */
    public function __construct(
        SubscriptionService $subscriptionService,
        UserService $userService)
    {
        parent::__construct($subscriptionService);
        $this->subscriptionService = $subscriptionService;
        $this->userService = $userService;
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        try {
			$data = (array)$data;
            $coupon = $this->validateCoupon($data);
            if ($coupon instanceof ApiProblem) {
                return $coupon;
            }
            unset($data['promoCode']); //safe remove

            if ($coupon && ($coupon->isFree() || $coupon->getPlan() == PlanService::YEAR_GIFT_CARD_PLAN)) {
                $subscription = $this->createFreeSubscription($data, $coupon);
            } else {

                //check current limitation of the system
                if ($coupon) {
                    $plan = $coupon->getPlan();
                    $supportPlans = [PlanService::SAFEKIDYEAR_PLAN, PlanService::SEATTLEYEAR_PLAN, PlanService::WSPTAYEAR_PLAN, PlanService::STUDENTYEAR_PLAN];

                    if (!empty($plan) && !in_array($plan, $supportPlans)) {
                        return new ApiProblem(
                            405,
                            sprintf('Invalid plan', $coupon->getPlan())
                        );
                    }
                }

                //is gift card buying
				if (!$coupon && ($data['plan'] == PlanService::YEAR_GIFT_CARD_PLAN ||  $data['plan'] == PlanService::FREE_GIFT_CARD_PLAN)) {
                    $subscription = $this->buyGiftCards($data);
				} else {
                    $subscription = $this->createPaidSubscription($data, $coupon);
                }
            }

            //update subscription creator
            if ($subscription instanceof Subscription) {
                $this->subscriptionService->updateSubscriptionCreator($subscription, $this->getIdentity());
            }

            //update owner of subscription in case of renew existing user
            if ($subscription instanceof Subscription
                && isset($data['isRenew'])
                && (bool)$data['isRenew'] == true
            ) {
                if (isset($data['plan']) && ($data['plan'] == PlanService::YEAR_GIFT_CARD_PLAN || $data['plan'] == PlanService::FREE_GIFT_CARD_PLAN)) {
                    //do nothing for buy gift card case
                } else {
                    $this->userService->updateUserSubscription($this->getIdentity(), $subscription);
                }
            }

            return $subscription;

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @param array $data
     * @return Subscription|ApiProblem
     */
    private function buyGiftCards(array $data)
    {
		//normal case
		if ($data['plan'] == PlanService::FREE_GIFT_CARD_PLAN){
			$inputFilter = $this->getFreeGiftCardInputFilter();
		}
		else{
			$inputFilter = $this->getGiftCardInputFilter();
		}
		$inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            return new ApiProblem(422, 'Failed Validation', null, null,
                ['validation_messages' => $inputFilter->getMessages()]
            );
        }

        if (!is_array($data['recipients']) || count($data['recipients']) <= 0) {
            return new ApiProblem(422, 'Failed Validation', null, null,
                ['validation_messages' => array('recipients' => 'Please enter recipients')]
            );
        }

        $data = $inputFilter->getValues();

        foreach ($data['recipients'] as $recipient) {
            $inputFilter = $this->getRecipientInputFilter();
            $inputFilter->setData($recipient);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    ['validation_messages' => $inputFilter->getMessages(),]
                );
            }
        }

        return $this
            ->subscriptionService
            ->buyGiftCards($data, $this->getIdentity());
    }


    /**
     * @param array $data
     * @param Coupon $coupon
     * @return Subscription|ApiProblem
     */
    private function createFreeSubscription(array $data, Coupon $coupon = null)
    {
        $inputFilter = $this->getFreeInputFilter();
        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            return new ApiProblem(422, 'Failed Validation', null, null,
                ['validation_messages' => $inputFilter->getMessages(),]
            );
        }

        return $this
            ->subscriptionService
            ->createFreeSubscription($inputFilter->getValues(), $coupon);
    }


    /**
     * @param array $data
     * @param Coupon $coupon
     * @throws \Exception
     * @return Subscription|ApiProblem
     */
    private function createPaidSubscription(array $data, Coupon $coupon = null)
    {
        //apple in-app purchase
        if (isset($data['packageName'])) {
            return $this->createInAppPurchase($data, $coupon);
        }

        //normal case
        $inputFilter = $this->getPaidInputFilter();
        $inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            return new ApiProblem(422, 'Failed Validation', null, null,
                ['validation_messages' => $inputFilter->getMessages(),]
            );
        }

        return $this
            ->subscriptionService
            ->createPaidSubscription($inputFilter->getValues(), $coupon);
    }

    /**
     * @param $data
     * @param Coupon $coupon
     * @return Subscription|bool|ApiProblem
     */
    public function createInAppPurchase($data, Coupon $coupon = null)
    {
        switch ($data['packageName']) {
            case PlanService::IOS_MONTHLY_SUBSCRIPTION:
            case PlanService::IOS_YEARLY_SUBSCRIPTION:
            case PlanService::IOS_MONTHLY_SUBSCRIPTION_AR:
            case PlanService::IOS_YEARLY_SUBSCRIPTION_AR:
                $inputFilter = $this->getAppleInAppInputFilter();
                break;

            case PlanService::ANDROID_MONTHLY_SUBSCRIPTION:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTION:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST:
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST1:
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST1:
                $inputFilter = $this->getGoogleInAppInputFilter();
                break;

            default:
                return new ApiProblem(422, 'Failed Validation', null, null,
                    ['validation_messages' => ['message' => 'The packageName ' . $data['packageName'] . ' is invalid'],]
                );
        }

        $inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            return new ApiProblem(422, 'Failed Validation', null, null,
                ['validation_messages' => $inputFilter->getMessages()]
            );
        }

        //verify IOS In-App Purchase
        $validationResult = $this
            ->subscriptionService
            ->verifyInAppPurchase($data);

        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->subscriptionService->createInAppPurchaseSubscription(
            $inputFilter->getValues(),
            $coupon
        );
    }

    /**
     * Patch (partial update) a resource by id
     * @param mixed $id
     * @param mixed $data
     * @return mixed|void|ApiProblem
     */
    public function patch($id, $data)
    {
        try {
            /** @var \Api\V1\Entity\Subscription $subscription */
            $subscription = $this->subscriptionService->find($id);
            if (!$subscription) {
                throw new EntityNotFoundException('Could not find subscription ' . $id);
            }

            $result = $this->isAuthorized($subscription, AclAuthorization::PERMISSION_UPDATE, false);
            if ($result !== true) {
                return $result;
            }

            $user = $this->getIdentity();
            //only works for expired user
            if (!$user->hasExpired()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    ['validation_messages' => ['UserNotExpired' => 'The user ' . $user->getFullName() . ' is not expired yet.']]
                );
            }

            if ($subscription->getUser()->getId() != $user->getId()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    ['validation_messages' => [
                        'UserMismatch' => 'The subscription ' . $subscription->getId()
                            . ' is not associated with user ' . $user->getFullName()
                    ]]
                );
            }

            $data = (array)$data;

            $data['packageName'] = $data['packageName'] ?: '';
            switch ($data['packageName']) {
                case PlanService::IOS_MONTHLY_SUBSCRIPTION:
                case PlanService::IOS_MONTHLY_SUBSCRIPTION_AR:
                case PlanService::IOS_YEARLY_SUBSCRIPTION:
                case PlanService::IOS_YEARLY_SUBSCRIPTION_AR:
                    $inputFilter = $this->getAppleInAppInputFilter();
                    break;

                case PlanService::ANDROID_MONTHLY_SUBSCRIPTION:
                case PlanService::ANDROID_YEARLY_SUBSCRIPTION:
                case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST:
                case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST:
                case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST1:
                case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST1:
                    $inputFilter = $this->getGoogleInAppInputFilter();
                    break;

                default:
                    return new ApiProblem(422, 'Failed Validation', null, null,
                        ['validation_messages' => ['message' => 'The packageName ' . $data['packageName'] . ' is invalid']]
                    );
            }

            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    ['validation_messages' => $inputFilter->getMessages()]
                );
            }

            //verify IOS In-App Purchase
            $validationResult = $this
                ->subscriptionService
                ->verifyInAppPurchase($data);

            if ($validationResult !== true) {
                return $validationResult;
            }

            $this
                ->subscriptionService
                ->updateSubscriptionBilling(
                    $subscription,
                    $this->getInputFilteredValues($inputFilter, $data)
                );
            $this
                ->userService
                ->updateUserSubscription(
                    $this->getIdentity(),
                    $subscription
                );

            return $subscription;
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @see Apigility/Doctrine/Server/Resource/AbstractResource.php
     * @param  array $params
     * @return ApiProblem|SubscriptionCollection
     */
    public function fetchAll($params = array())
    {
        try {
            $queryParams = $this->getQueryParams();

            //restrict to only when network corrupted and client wants to re-get subscription to verify
            if (isset($queryParams['query']['receiptId'])) {

                $inputFilter = $this->getReAccessSubscriptionInputFilter();
                $inputFilter->setData($queryParams['query']);

                if (!$inputFilter->isValid()) {
                    return new ApiProblem(422, 'Failed Validation', null, null,
                        ['validation_messages' => $inputFilter->getMessages(),]
                    );
                }

                return $this->dataService->fetchAll(
                    $queryParams,
                    null,
                    null,
                    $this->getCollectionClass()
                );
            }
            //use default
            return parent::fetchAll($params);
        } catch (\Exception $e) {
            return $this->processUnhandledException($e);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_SUBSCRIPTION;
    }
}
