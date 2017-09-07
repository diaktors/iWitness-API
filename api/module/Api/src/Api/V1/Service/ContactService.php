<?php

namespace Api\V1\Service;

use Api\V1\Entity\Contact;
use Api\V1\Service\Exception\BusinessException;
use Doctrine\ORM\EntityManager;
use Webonyx\Util\UUID;
use Psr\Log\LoggerInterface;
use Api\V1\Service\Extension\TokenTrait;

class ContactService extends ServiceAbstract
{
    use TokenTrait;

    const ENTITY_CLASS = 'Api\V1\Entity\Contact';

    const RESET_PASSWORD_TOKEN_EXPIRE_HOURS = 6;
    const CONTACT_CONFIRM_ROLE = 'contact_confirm';


    /** @var Config */
    private $config = null;


    /**
     * @param array $config
     * @param EntityManager $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        array $config,
        EntityManager $entityManager,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->config = $config;
    }

    /**
     * @param $data
     * @param $user
     * @return Contact
     */
    public function createContact($data, $user)
    {
        $data['created'] = time();
        $contact = new Contact(UUID::generate());
        $contact->setUser($user);
        $contact->setFlags(Contact::PENDING);

        $this->hydrator->hydrate($data, $contact);
        $this->entityManager->persist($contact);

        // flush all data to database
        $this->entityManager->flush();
        return $contact;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param $contact \Api\V1\Entity\Contact
     * @param  $data \Array
     * @internal param mixed $id
     * @return ApiProblem|mixed
     */
    public function update($contact, $data)
    {
        $reConfirm = false;
        if ((!empty($data['email']) && $contact->getEmail() != $data['email']) || (!empty($data['phone']) && $contact->getPhone() != $data['phone'])) {
            $reConfirm = true;
        }

        // update contact
        $data['updated'] = time();
        $originalData = $this->hydrator->extract($contact);
        $patchedData = array_merge($originalData, $data);

        $this->hydrator->hydrate($patchedData, $contact);

        // flush all data to database
        $this->entityManager->flush();

        return $reConfirm;
    }


    /**
     * Confirm contact
     * @param $token
     * @param $decline
     * @return Contact
     * @throws Exception\BusinessException
     */
    public function confirm($token, $decline)
    {
        /** @var  \Api\V1\Entity\Contact $contact */
        $contact = $this->assertValidToken($token, self::CONTACT_CONFIRM_ROLE);
        if ($contact) {
            $status = $contact->getStatus();
            if (!$status->issetBits(Contact::PENDING)) {
                //throw new BusinessException('Invalid contact status ', 404);
                throw new BusinessException('Already confirmed', 404);
            }

            if ($decline) {
                $contact->setStatus(Contact::DECLINED);
            } else {
                $contact->setStatus(Contact::ACCEPTED);
            }

            $this->entityManager->merge($contact);
            $this->entityManager->flush($contact);
        }
        return $contact;
    }


    /**
     * Get contact repository out of service
     * @deprecated this function is used in check duplicate Contact only, please don't use it elsewhere
     * @return \Doctrine\ORM\EntityRepository|null
     */
    public function getContactRepository()
    {
        return $this->getRepository();
    }
} 
