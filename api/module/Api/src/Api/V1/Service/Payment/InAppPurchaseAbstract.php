<?php

namespace Api\V1\Service\Payment;

use Doctrine\ORM\EntityManager;
use Perpii\InputFilter\InputFilterTrait;
use Perpii\Log\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ZF\ApiProblem\ApiProblem;


abstract class InAppPurchaseAbstract implements LoggerAwareInterface
{
    use LoggerTrait;

    protected $config;

    /** @var \Doctrine\ORM\EntityRepository */
    protected $subscriptionRepository;

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        EntityManager $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->setLogger($logger);
        $this->subscriptionRepository = $entityManager->getRepository('Api\V1\Entity\Subscription');
    }

    /**
     * @param array $data
     * @return boolean|ApiProblem
     */
    abstract function verifyInAppPurchase(array $data);

    /**
     * @return string
     */
    abstract function getName();

} 