#!/usr/bin/php
<?php
require dirname(__FILE__) . '/../Bootstrap.php';


use Api\V1\Entity\Subscription;
use Api\V1\Service\SettingService;
use Api\V1\Service\SubscriptionService;
use Api\V1\Service\UserService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Zend\Json;
use Zend\ServiceManager\ServiceManager;

class PaypalSubscriptionStatusUpdater implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var  ServiceManager;
     */
    private $serviceManager = null;

    /** @var \AuthorizeNetTD */
    private $authorizeNetTD = null;

    /** @var array */
    private $config = null;

    /** @var UserService */
    private $userService = null;

    /** @var SubscriptionService */
    private $subscriptionService = null;

    /** @var SettingService */
    private $settingService = null;

    /** @var EntityManager */
    private $entityManager = null;


    public function __construct()
    {
        Bootstrap::initializeLogger('logger-email');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));

        //configuration
        $config = $this->serviceManager->get('config');
        $this->config = $config['paymentGateWays']['webCheckout'];

        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');

        $this->userService = $this->serviceManager->get('Api\V1\Service\UserService');
        $this->subscriptionService = $this->serviceManager->get('Api\V1\Service\SubscriptionService');
        $this->settingService = $this->serviceManager->get('Api\\V1\\Service\\SettingService');

        //Authorize.Net  Advanced Integration Method
        $this->authorizeNetTD = new \AuthorizeNetTD(
            $this->config['Authorize.Net']['loginId'],
            $this->config['Authorize.Net']['transactionKey']
        );

        if ($this->config['sandbox']) {
            $this->authorizeNetTD->setSandbox(true);
        }

        $this->authorizeNetTD->setLogFile($this->config['Authorize.Net']['log']);
    }

    /**
     * Execute Automated Recurring Billing (ARB) updating
     */
    public function  run()
    {
        try {
            //1. get last time running
            $setting = $this->settingService->getLastAuthorizeNetUpdate();
            if ($setting == 0) {
                //$setting = mktime(0, 0, 0, 8, 21, 2014); //start date of new system 2.0
                $setting = mktime(0, 0, 0, 6, 21, 2014); //start date of new system 2.0
            }

            //2. calculate period, maximum is 31 days
            $current = time();
            $start = $setting;
            $end = ($setting + 172800) > $current ? $current : $setting + 172800; //2 days a chunk

            do {
                //3. process period
                $firstSettlementDate = substr(date('c', $start), 0, -6);
                $lastSettlementDate = substr(date('c', $end), 0, -6);
                $this->processDuration($firstSettlementDate, $lastSettlementDate);

                $this->settingService->saveLastAuthorizeNetUpdate($end);
                $start = $end;
                $end = ($end + 172800) > $current ? $current : $end + 172800;

            } while ($start < $current);

            //4. update last run to database
            $this->debug('End of process subscription status ');
        } catch (\Exception $ex) {
            $this->debug($ex->getMessage());
        }
    }

    /**
     * @param $firstSettlementDate
     * @param $lastSettlementDate
     */
    private function processDuration($firstSettlementDate, $lastSettlementDate)
    {
        $this->debug('Process from ' . $firstSettlementDate . ' to ' . $lastSettlementDate);

        $response = $this->authorizeNetTD->getSettledBatchList(false, $firstSettlementDate, $lastSettlementDate);
        if ($response->isError()) {
            $this->error($response->getErrorMessage());
            return;
        }

        $this->debug(count($response->xml->batchList->batch) . " batches");

        if (!isset($response->xml->batchList->batch)) {
            return; //nothing to process
        }

        foreach ($response->xml->batchList->batch as $batch) {
            $this->processPatch($batch->batchId);
        }
    }

    /**
     * @param $batchId
     */
    private function processPatch($batchId)
    {
        $this->debug('Process path id =' . $batchId);

        $response = $this->authorizeNetTD->getTransactionList($batchId);

        if ($response->isError()) {
            $this->error($response->getErrorMessage());
            return;
        }

        if (!isset($response->xml->transactions->transaction)) {
            return; //nothing to process
        }

        $this->debug(count($response->xml->transactions->transaction) . " transaction");

        foreach ($response->xml->transactions->transaction as $transaction) {
            $this->processTransaction($transaction->transId);
        }
    }

    /**
     * @param $transId
     */
    private function processTransaction($transId)
    {
        $this->debug('Process transaction id =' . $transId);

        $response = $this->authorizeNetTD->getTransactionDetails($transId);

        if ($response->isError()) {
            $this->error($response->getErrorMessage());
            return;
        }

        if (isset($response->xml->transaction->subscription)) {
            $this->debug('This is a auto renew transaction = ' . $transId);
            $this->updateStatus($response->xml->transaction);
        } else {
            $this->debug('This is not an auto renew transaction = ' . $transId . ', ignore it');
        }
    }

    /**
     * @param $transaction
     * @internal param $subscriptionId
     * @internal param $submitTimeUTC
     */
    private function  updateStatus(\SimpleXMLElement $transaction)
    {
        //1. check if transaction has been updated
        $subByBilling = $this->subscriptionService->findReceiptId($transaction->transId);

        if ($subByBilling) {
            $this->debug('receipt id = ' . $subByBilling->getReceiptId());

            $this->debug('Transaction id ' . $transaction->transId . ' has been updated already. Ignore it.');
            return; //ignore it
        }

        $this->debug('Update new transaction id =' . $transaction->transId);

        $paypalSub = $transaction->subscription;
        $subByArb = $this->subscriptionService->findByArb($paypalSub->id);
        if (!$subByArb) {
            $this->error('There is something wrong in your system. ARB id =' . $paypalSub->id . ' does not exist. Please verify manually');
            return;
        }

        //2. get user by subscription id
        $user = $subByArb->getUser();
        if (!$user) {
            $this->error('There is something wrong in your system. Subscription id = ' . $subByArb->getId() . ' does not have a user associate with it');
            return;
        }

        //3. create new subscription for user
        /** @var Subscription $subscription */
        $subscription = new Subscription(\Webonyx\Util\UUID::generate());

        $submitTimeUTC = self::parseDate($transaction->submitTimeUTC);

        $endDate = new DateTime();
        $endDate->setTimestamp($submitTimeUTC);
        $endDate = $endDate->add(\DateInterval::createFromDateString("1 months"));
        $endDate->setTime(23, 59, 59);

        $subscription->setUser($user)
            ->setReceiptId($transaction->transId)
            ->setPlan(\Api\V1\Service\PlanService::MONTH_PLAN)
            ->setStartAt($submitTimeUTC)
            ->setExpireAt($endDate->getTimestamp())
            ->setAmount($transaction->authAmount)
            ->setPaymentGateway('Authorize.Net')
            ->setArbBillingId($paypalSub->id);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush($subscription);
        $this->userService->updateUserSubscription($user, $subscription);

        $this->debug('One subscription id = ' . $subscription->getId() . ' has been created');
    }


    /**
     * @param $submitTimeUTC
     * @return int
     */
    function parseDate($submitTimeUTC)
    {
        $year = substr($submitTimeUTC, 0, 4);
        $month = substr($submitTimeUTC, 5, 2);
        $day = substr($submitTimeUTC, 8, 2);
        $hour = substr($submitTimeUTC, 11, 2);
        $min = substr($submitTimeUTC, 14, 2);
        $second = substr($submitTimeUTC, 17, 2);
        return mktime($hour, $min, $second, $day, $month, $year);
    }


    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

}


$subscriptionStatus = new PaypalSubscriptionStatusUpdater();
$subscriptionStatus->run();