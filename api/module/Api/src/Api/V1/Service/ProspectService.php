<?php

namespace Api\V1\Service;


use Api\V1\Entity\Prospect;
use Doctrine\ORM\EntityManager;
use Perpii\Util\String;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;

class ProspectService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\Prospect';

    /** @var Config */
    private $config = null;

    public function __construct(
        array $config,
        EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->config = $config;
        parent::__construct($entityManager, $logger);
    }

    /**
     * @param $platform
     * @param $email
     * @return \Api\V1\Entity\Prospect
     */
    public function insertOrUpdate($platform, $email)
    {
        $prospect = $this->getRepository()->insertOrUpdate($platform, $email);
        return $prospect;
    }
} 