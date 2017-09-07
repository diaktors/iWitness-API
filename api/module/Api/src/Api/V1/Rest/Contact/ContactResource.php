<?php
namespace Api\V1\Rest\Contact;

use Api\V1\Resource\ResourceAbstract;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Service\ContactService;
use Api\V1\Service\UserService;
use Aws\CloudFront\Exception\Exception;
use Perpii\Message\EmailManager;
use Perpii\Message\SmsManager;
use Webonyx\Util\UUID;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;

class ContactResource extends ResourceAbstract
{
    use ContactValidatorTrait;

    /** @var  $config array */
    private $config;
    /**
     * @var EmailManager
     */
    private $emailManager;
    /**
     * @var SmsManager
     */
    private $smsManager;

    /**
     * @var \Api\V1\Service\UserService
     */
    private $userService;

    public function __construct(array $config, ContactService $contactService, UserService $userService, EmailManager $emailManager, SmsManager $smsManager)
    {
        parent::__construct($contactService);

        $this->config = $config;
        $this->emailManager = $emailManager;
        $this->smsManager = $smsManager;
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
            $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_CREATE);
            if ($result !== true) {
                return $result;
            }

            $data = (array)$data;
            $inputFilter = $this->getInputFilters($data);
            //validate and filter data
            $inputFilter->setData($data);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            $data = $inputFilter->getValues();
            $contact = $this
                ->getContactService()
                ->createContact(
                    $inputFilter->getValues(),
                    $this->getUserService()->find($data['userId'])
                );

            // send an email to notify for user
			$this->sendConfirmationEmail($contact);

            // send an email to no`2yytify for user
            $this->sendConfirmationSms($contact);

            return $contact;
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
            /** @var \Api\V1\Entity\Contact */
            $contact = $this->getContactService()->find($id);
            if (!$contact) {
                return new ApiProblem(404, 'Contact with id ' . $id . ' was not found');
            }

            $result = $this->isAuthorized($contact, AclAuthorization::PERMISSION_UPDATE);
            if ($result !== true) {
                return $result;
            }
            //print_r($contact);exit;
            $data = (array)$data;
            $inputFilter = $this->getInputFilters($data, $contact, false);

            //validate and filter data
            $inputFilter->setData($data);

            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }
             
            $reConfirm = $this
                ->getContactService()
                ->update($contact, $this->getInputFilteredValues($inputFilter, $data));

            if ($reConfirm) {
                $this->sendConfirmationEmail($contact);
                $this->sendConfirmationSms($contact);
            }

            return $contact;
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @override delete function from base class
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        try {
            /** @var \Api\V1\Entity\Contact */
            $contact = $this->getContactService()->find($id);
            if (!$contact) {
                return new ApiProblem(404, 'Contact with id ' . $id . ' was not found');
            }

            $result = $this->isAuthorized($contact, AclAuthorization::PERMISSION_DELETE);
            if ($result !== true) {
                return $result;
            }

            $email = $contact->getEmail(); //strange why you did it?
            $succeed = $this->getContactService()->delete($contact);

            if (!$succeed) {
                return new ApiProblem(500, "Could not delete {$id}");
            }

            return new ApiProblem(200, array('email' => $email));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
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
            $routeMatch = $this->getEvent()->getRouteMatch();
            $userId = $routeMatch->getParam('user_id', null);
            $queryParams = $this->getQueryParams();

            if (empty($userId) && isset($queryParams['query']['user_id'])) {
                $userId = $queryParams['query']['user_id'];
            }

            if ($userId) {
                unset($queryParams['query']['user_id']);
                $queryParams['query']['userId'] = UUID::toBinary($userId);
                $user = $this->userService->find($userId);
                $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_VIEW, false);
            } else {
                $result = $this->isAuthorized($this->getResourceId(), AclAuthorization::PERMISSION_LIST_ALL);
            }
            if ($result !== true) {
                return $result;
            }
            return $this
                ->getContactService()
                ->fetchAll($queryParams, null, null, $this->getCollectionClass());
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return ContactService
     */
    private function getContactService()
    {
        return $this->dataService;
    }

    private function getUserService()
    {
        return $this->userService;
    }

    /**
     * Send email confirmation for contact
     *
     * @param $contact \Api\V1\Entity\Contact
     * @return ApiProblem|mixed
     */
    private function sendConfirmationEmail($contact)
    {
        try {
            $user = $this->getIdentity();
            $webConfig = $this->config['web'];
            $connectionUrl = $webConfig['secureBaseUrl'] . '/friend-connect';

            $token = $this->getContactService()->generateToken($contact, ContactService::CONTACT_CONFIRM_ROLE);

            $vars = array(
                'from' => $user,
                'to' => $contact,
                'webConfig' => $webConfig,
                'accept' => $connectionUrl . "?secret_key={$token}",
                'decline' => $connectionUrl . "?secret_key={$token}&decline=1"
            );


            $this->emailManager
                ->setReceiver($contact->getEmail())
                ->setSenderName($user->getFullName())
                ->setSenderEmail($user->getEmail())
                ->setTemplate('/contact/confirmation-email.phtml')
                ->setTemplateData($vars)
                ->send();
        } catch (\Exception $ex) {
            $this->error('Couldn\'t send email to ' . $contact->getEmail(), ['exception' => $ex]);
        }
	}

    /**
     * Send sms confirmation for contact
     *
     * @param $contact \Api\V1\Entity\Contact
     * @return ApiProblem|mixed
     */
    private function sendConfirmationSms($contact)
    {
        try {
            $user = $this->getIdentity();
            $webConfig = $this->config['web'];
            $connectionUrl = $webConfig['secureBaseUrl'] . '/friend-connect';

            $token = $this->getContactService()->generateToken($contact, ContactService::CONTACT_CONFIRM_ROLE);
            
            $data = array(
                'name' => $user->getFullName(),
				//'accept' => $connectionUrl . "?secret_key={$token}",
				'alertLink' => "/friend-connect?secret_key={$token}",
				//'message' => 'I need friends/family to be my trusted contacts. You would get a text or email in case of emergency and reach out to make sure I am OK.',
				'message' => 'Please be a trusted contact in case of emergency.',
				'template' => 0,
				'safe' => 'contact',
			);
		    $this->smsManager
				->setSender($user->getPhone())
				->setReceiver($contact->getPhone())
				->setTemplateData($data)
				//->setTemplate('/contact/confirmation-sms.phtml')
				->send();

        } catch (\Exception $ex) {
            $this->error('Couldn\'t send sms to ' . $contact->getPhone(), ['exception' => $ex]);
		}
	}

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_CONTACT;
    }
}
