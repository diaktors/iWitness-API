<?php
namespace Api\V1\Rest\Invitation;

use Api\V1\Controller\ValidationException;
use Api\V1\Controller\ValidationResult;
use Perpii\InputFilter\InputFilterTrait;
use Api\V1\Security\Authorization\AclAuthorization;
use Perpii\Message\EmailManager;
use Api\V1\Resource\ResourceAbstract;
use Zend\InputFilter\InputFilter;
use ZF\ApiProblem\ApiProblem;
use Api\V1\Resource\ResourceFactoryTrait;

class InvitationResource extends ResourceAbstract
{
    use InputFilterTrait;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @param EmailManager $emailManager
     */
    public function __construct(EmailManager $emailManager)
    {
        parent::__construct(null);
        $this->emailManager = $emailManager;
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
            //validate sender  parts
            $inputFilter = $this->getTellAFriendInputValidator($data);
            if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $inputFilter->getMessages(),
                ));
            }

            $sender = $inputFilter->getValues();

            if (isset($data['friendEmails'])) {
                //old api
                $sent = $this->processFriendEmails($data['friendEmails'], $sender);
            } elseif (isset($data['friends'])) {
                //new api
                $sent = $this->processFriendObjects($data['friends'], $sender);
            } else {
                //missing
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => array('friends' => 'friends is required field.'),
                ));
            }

            $result = new Invitation();
            $result->setResponse($sent);

            return $result;
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @param $friendEmails
     * @param $sender
     * @return array
     * @throws \Api\V1\Controller\ValidationException
     */
    private function processFriendEmails($friendEmails, $sender)
    {
        $validationResult = $this->validateFriendEmails($friendEmails);
        if (!$validationResult->isValid()) {
            throw ValidationException::create($validationResult);
        }

        $sent = array();
        foreach ($validationResult->getValues() as $friendEmail) {
            if ($this->sendTellAFriendEmail($friendEmail, null, $sender)) {
                $sent[] = $friendEmail;
            }
        }
        return $sent;
    }


    private function processFriendObjects($friends, $sender)
    {
        $validationResult = $this->validateFriendObjects($friends);
        if (!$validationResult->isValid()) {
            throw ValidationException::create($validationResult);
        }

        $sent = array();
        foreach ($validationResult->getValues() as $friend) {
            $friendEmail = $friend['email'];
            $friendName = isset($friend['name']) ? $friend['name'] : null;
            if ($this->sendTellAFriendEmail($friendEmail, $friendName, $sender)) {
                $sent[] = $friendEmail;
            }
        }

        return $sent;
    }


    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }


    /**
     * @param $friendEmail
     * @param $friendName
     * @param $sender
     * @return boolean
     */
    private function sendTellAFriendEmail($friendEmail, $friendName, $sender)
    {
        $vars = $sender + array('to' => $friendEmail, 'name' => $friendName);
        try {
            $this
                ->emailManager
                ->setSenderName($sender['firstName'] . ' ' . $sender['lastName'])
                ->setSenderEmail($sender['email'])
                ->setReceiver($friendEmail)
                ->setTemplate('/user/tell-a-friend-email.phtml')
                ->setTemplateData($vars)
                ->send();
        } catch (\Exception $ex) {
            $this->error('Couldn\'t send email to ' . $friendEmail, ['exception' => $ex]);
            return false;
        }

        return true;
    }

    /**
     * @param Array $data
     * @return InputFilter
     */
    private function getTellAFriendInputValidator($data)
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'email', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\EmailAddress'),
                ),
            ))
            ->add(array('name' => 'firstName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'lastName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'subject', 'required' => true, 'allow_empty' => false))
            //->add(array('name' => 'friendEmails', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'message', 'required' => true, 'allow_empty' => false));

        $inputFilter->setData($data);
        return $inputFilter;
    }

    /**
     * @param array $friendEmails
     * @return ValidationResult
     */
    //todo: this support olf API, should be removed after all mobiles upgrade to new version
    private function validateFriendEmails($friendEmails)
    {
        $validationResult = new ValidationResult();

        //check input data
        if (empty($friendEmails)) {
            $validationResult->addError('friends', 'Friends cannot be empty');
        }

        $friendEmails = (array)$friendEmails;
        $index = 1;
        foreach ($friendEmails as $friendEmail) {
            $email = trim($friendEmail);
            if (strlen($email) <= 0) {
                continue;
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationResult->addValue(null, $email);
            } else {
                $validationResult->addError("friend_email_$index", 'Invalid email format');
            }
            $index++;
        }
        return $validationResult;
    }

    /**
     * @param $friends
     * @return ValidationResult
     */
    private function validateFriendObjects($friends)
    {
        $validationResult = new ValidationResult();

        //check input data
        if (empty($friends)) {
            $validationResult->addError('friends', 'Friends cannot be empty');
        }

        $friends = (array)$friends;
        $index = 1;
        foreach ($friends as $friend) {
            if (isset($friend['email'])) {
                $email = trim($friend['email']);
                if (strlen($email) <= 0) {
                    continue;
                }

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $friend['email'] = $email;
                    $validationResult->addValue(null, $friend);
                } else {
                    $validationResult->addError("friend_email_$index", 'Invalid email format');
                }
            }
            $index++;
        }

        return $validationResult;
    }


    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_INVITATION;
    }

}
