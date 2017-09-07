<?php

namespace Api\V1\Service;

use Api\V1\Entity\Message;
use Api\V1\Entity\User;
use Api\V1\Entity\UserMessage;
use Doctrine\ORM\EntityManager;
use Webonyx\Util\UUID;
use Psr\Log\LoggerInterface;

class MessageService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\Message';

    /** @var Config */
    private $config = null;

    public function __construct(
        array $config,
        EntityManager $entityManager, LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->config = $config;
    }

    /**
     * Insert data for device and user device
     *
     * @param $message
     * @param \Api\V1\Entity\User $user
     * @return Device
     */
    public function insertUserMessage($message, User $user)
    {
        $messageEntity = new Message(UUID::generate());
        $messageEntity->setMessage($message);
        $this->entityManager->persist($messageEntity);

        // populate data for UserMessage
        $userMessage = new UserMessage(UUID::generate());
        $userMessage->setMessage($messageEntity);
        $userMessage->setUser($user);
        $userMessage->setRead('0');
        $this->entityManager->persist($userMessage);

        // commit everything to database
        $this->entityManager->flush();
        return $messageEntity;
    }

    /**
     * @param User $user
     * @return array of Messages
     */
    public function findActiveByUser(User $user)
    {
        return $this->getRepository()->findActiveByUser($user);
    }
} 