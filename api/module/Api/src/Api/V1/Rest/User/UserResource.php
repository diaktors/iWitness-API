<?php
namespace Api\V1\Rest\User;

use Api\V1\Resource\ResourceAbstract;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\DeviceService;
use Api\V1\Service\UserService;
use Herrera\Phar\Update\Exception\Exception;
use Perpii\InputFilter\Filter\NormalizePhoneFilter;
use Perpii\Message\EmailManager;
use Perpii\Message\SmsManager;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class UserResource extends ResourceAbstract
{
    use UserValidatorTrait;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var SmsManager
     */
    private $smsManager;

    /** @var  UserService */
    private $userService;

    /** @var  DeviceService */
    private $deviceService;

    public function __construct(UserService $userService,
                                EmailManager $emailManager,
                                SmsManager $smsManager,
                                DeviceService $deviceService)
    {
        parent::__construct($userService);
        $this->userService = $userService;
        $this->emailManager = $emailManager;
        $this->smsManager = $smsManager;
        $this->deviceService = $deviceService;
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

            $inputFilter = $this->getCreatingInputFilter($data);
            $inputFilter->setData($data);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    array('validation_messages' => $inputFilter->getMessages())
                );
            }

            //if subscription exist must check it
            $subscription = null;
            $subscriptionUuid = $inputFilter->has('subscriptionUuid') ? $inputFilter->getValue('subscriptionUuid') : null;
            $validateResult = $this->getUserService()->validateSubscription($subscriptionUuid);
            if ($validateResult instanceof ApiProblem) {
                return $validateResult;
            } else {
                $subscription = $validateResult;
            }

            $params = $inputFilter->getValues();

			$user = $this->getUserService()->createUser($params, $subscription);

			$orig_phn_model = $subscription->getOriginalPhoneModel();

			//error_log( "Original phone model: ".$orig_phn_model , 3, "/volumes/log/api/test-log.log");
			//error_log( print_r($data,TRUE),  3, "/volumes/log/api/test-log.log");

			if ($orig_phn_model =='android'){
				//$link = "http://bit.ly/HDveIh";
				$link = "http://bit.ly/2fBO4Uz";
			}else{
				$link = "http://bit.ly/1dn7WBk";
            }
            $this->sendWelcomeEmail($user);
            $this->sendWelcomeSms($user, $link);

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }

        return $user;
    }


    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        try {
            $user = $this->getIdentity();

            //not login user
            if ($user === null) {
                return new ApiProblem(401, 'Unauthorized');
            }
            return $this->getUserService()->fetchUsers($user, $this->getQueryParams(), $this->getCollectionClass());

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        try {
            /** @var \Api\V1\Entity\User |  \Api\V1\Entity\Admin $user */
            $user = $this->getUserService()->getUserById($id);
            if (!$user) {
                return new ApiProblem(404, 'User with id ' . $id . ' was not found');
            }

            $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_UPDATE, false);
            if ($result !== true) {
                return $result;
            }

            $data = (array)$data;
            $inputFilter = $this->getUpdatingInputFilter($id, $data);
			//error_log( print_r($inputFilter,TRUE),  3, "/volumes/log/api/test-log.log");
			//error_log( print_r($inputFilter->getMessages(),TRUE),  3, "/volumes/log/api/test-log.log");

            //check password changing
            $passwordValidateResult = $this->validatePasswordChange($user, $data);
            if ($passwordValidateResult !== true) {
                return $passwordValidateResult;
            }

            //change login phone number
            $passwordValidateResult = $this->validatePhoneChange($user, $data);
            if ($passwordValidateResult !== true) {
                return $passwordValidateResult;
            }

            //validate and filter data
            $inputFilter->setData($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    array('validation_messages' => $inputFilter->getMessages())
                );
            }

            $filteredValues = $this->getInputFilteredValues($inputFilter, $data);

            //if subscription exist, must check it
            $subscription = null;
            $subscriptionUuid = $inputFilter->has('subscriptionUuid') ? $inputFilter->getValue('subscriptionUuid') : null;
            //does not update if it is the same
            if ($subscriptionUuid && $subscriptionUuid != $user->getSubscriptionId()) {
                $validateResult = $this->getUserService()->validateSubscription($subscriptionUuid, $user->getId());
                if ($validateResult !== true) {
                    return $validateResult; //false
                } else {
                    $subscription = $validateResult;
                }
            }

            //validate suspend changing
            if (isset($filteredValues['suspended']) && $this->isAdmin()) {
                $user->toggleSuspended();
            }

            $user = $this->getUserService()->updateUser($user, $filteredValues, $subscription);

            //TODO: notify user's profile updated to devices
			error_log( print_r($user,TRUE),  3, "/volumes/log/api/test-log.log");

            return $user;

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @param $user \Api\V1\Entity\User
     * @internal param $subscription
     */
    private function sendWelcomeEmail($user)
    {
        $vars = array('to' => $user);

        try {
            $this
                ->emailManager
                ->setReceiver($user->getEmail())
                ->setTemplate('/user/welcome-email.phtml')
                ->setTemplateData($vars)
                ->send();
        } catch (\Exception $ex) {
            $this->error('Couldn\'t send email to ' . $user->getEmail(), ['exception' => $ex]);
        }
    }

    /**
     * @param \Api\V1\Entity\User $user
     */
    private function sendWelcomeSms($user, $link)
    {
		try {

			$smsdata = array(
				'link'       => $link,
				'template'   => 2,
				'safe'       => 'welcome'

			);
            $this
                ->smsManager
				->setReceiver(NormalizePhoneFilter::appendUSCanadaCountryCode($user->getPhone()))
				->setTemplateData($smsdata)
                //->setTemplate('/user/welcome-sms.phtml')
                ->send();
        } catch (\Exception $ex) {
            $this->error('Couldn\'t send sms to ' . $user->getPhone(), ['exception' => $ex]);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_USER;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->dataService;
    }
}
