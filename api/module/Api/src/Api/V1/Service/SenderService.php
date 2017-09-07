<?php

namespace Api\V1\Service;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class SenderService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\Sender';

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger = null)
    {
        parent::__construct($entityManager, $logger);
    }

    /**
     * @param $name
     * @param $email
     * @return mixed
     */
    public function insertOrUpdate($name, $email)
    {
        return $this->getRepository()->insertOrUpdate($name, $email);
    }
}