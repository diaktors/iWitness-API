<?php

namespace Api\V1\Service\Payment;

use Perpii\Log\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ZF\ApiProblem\ApiProblem;

abstract class  PaymentAbstract implements LoggerAwareInterface
{
    use LoggerTrait;

    protected $config;


    public function __construct(
        array $config,
        LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->config = $config;
    }


    /**
     * @param array $data
     * @return PaymentResult
     */
    abstract function createBilling(array $data);

    /**
     * Create Automated Recurring Billing
     * @param array $data
     * @return PaymentResult
     */
    abstract function createARBBilling(array $data);

    /**
     * @return string
     */
    abstract function getName();
}