<?php

namespace Api\V1\Rpc\Contact;

use Api\V1\Controller\BaseActionController;
use Api\V1\Entity\Contact;
use Api\V1\Entity\User;
use Api\V1\Hydrator\ContactHydrator;
use Api\V1\Hydrator\UserHydrator;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Service\ContactService;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Psr\Log\LoggerInterface;
use Perpii\Message\EmailManager;

class ContactController extends BaseActionController
{

    /** @var \Api\V1\Service\ContactService */
    private $contactService;

    /**
     * @var EmailManager
     */
    private $emailManager;


    /**
     * @var \Api\V1\Hydrator\ContactHydrator
     */
    private $contactHydrator;

    /**
     * @var UserHydrator
     */
    private $userHydrator;


    /**
     * @param \Api\V1\Security\Authentication\AuthenticationServiceInterface $authentication
     * @param AuthorizationInterface $authorization
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Api\V1\Service\ContactService $contactService
     * @param EmailManager $emailManager
     * @param \Api\V1\Hydrator\ContactHydrator $contactHydrator
     * @param \Api\V1\Hydrator\UserHydrator $userHydrator
     * @internal param \Api\V1\Rpc\Contact\PhotoService $photoService
     * @internal param \Api\V1\Service\UserService $userService
     */
    public function __construct(AuthenticationServiceInterface $authentication,
                                AuthorizationInterface $authorization,
                                LoggerInterface $logger,
                                ContactService $contactService,
                                EmailManager $emailManager,
                                ContactHydrator $contactHydrator,
                                UserHydrator $userHydrator)
    {
        parent::__construct($authentication, $authorization, $logger);
        $this->contactService = $contactService;
        $this->emailManager = $emailManager;
        $this->contactHydrator = $contactHydrator;
        $this->userHydrator = $userHydrator;
    }

    /**
     * @return JsonModel|ApiProblemModel
     */
    public function validateTokenAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $token = $router->getParam('token');

            if (empty($token)) {
                throw new \Exception('Token is required', 417);
            }

            $contact = $this->contactService->assertValidToken(
                $token,
                ContactService::CONTACT_CONFIRM_ROLE
            );

            if ($contact) {
                return new JsonModel(
                    array(
                        'status' => '200',
                        'message' => 'Token is valid',
                        'contact' => $this->contactHydrator->extract($contact),
                        'user' => $this->userHydrator->extract($contact->getUser())
                    )
                );
            }

            return new JsonModel(array('status' => '404', 'message' => 'Token is invalid'));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return JsonModel|ApiProblemModel
     */
    public function confirmAction()
    {
        try {
            $content = json_decode($this->getRequest()->getContent(), true);
            if (!isset($content['token']) || empty($content['token'])) {
                throw new \Exception('Token is required', 417);
            }

            $token = $content['token'];
            $decline = isset($content['decline']) ? (bool)$content['decline'] : false;

            $contact = $this->contactService->confirm($token, $decline);

            if ($contact) {
                if ($contact->getStatus()->issetBits(Contact::ACCEPTED)) {
                    $this->sendConfirmedEmail($contact);
                }
                return new JsonModel(array(
                    'status' => '200',
                    'message' => 'Contact confirmation successfully.',
                    'contact' => $this->contactHydrator->extract($contact),
                    'user' => $this->userHydrator->extract($contact->getUser())
                ));
            }
            return new JsonModel(array('status' => '404', 'message' => 'Unknown exception'));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @param Contact $contact
     * @return \ZF\ApiProblem\View\ApiProblemModel
     */
    private function sendConfirmedEmail(Contact $contact)
    {
        $user = $contact->getUser();
        try {
            $this
                ->emailManager
                ->setReceiver($user->getEmail())
                ->setTemplate('/contact/confirmed-email.phtml')
                ->setTemplateData(
                    array('contact' => $contact)
                )
                ->send();
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
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