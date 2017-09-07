<?php

namespace Api\V1\Service;

use Api\V1\Repository\PlanRepository;
use Api\V1\Entity\Coupon;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;

class CouponService extends ServiceAbstract
{

    const ENTITY_CLASS = 'Api\V1\Entity\Coupon';

    /** @var PlanRepository */
    private $planRepository = null;

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger = null)
    {
        parent::__construct($entityManager, $logger);

        $this->planRepository = $entityManager->getRepository('Api\V1\Entity\Plan');
    }

    /**
     * @deprecated this function is used in check duplicate User only, please don't use it elsewhere
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getCouponRepository()
    {
        return $this->getRepository();
    }

    /**
     * @param $data
     * @throws \Api\V1\Repository\Exception
     * @throws \Exception
     * @return Coupon|null
     */
    public function createCoupons($data)
    {
        $coupon = null;
        $numberOfCode = isset($data['numberOfCode']) ? intval($data['numberOfCode']) : 1;

        //todo: temporary fix for system running, in the Coupon must be built more generic
        //it means there is no pre-defined plans like safekids, seattle university, giftcards
        //all of these plans must be created admin at run time. He could create whatever promotion campaign he want.
        //there are no hard code plans like safekids, seattle, ...
        //As a point of view of Dev I don't like theses Exceptions because it makes application very complicated, hard to code
        //and error prone.
        //There is a limitation of current application is admin cannot create a campaign like creating a voucher that will
        //reduce amount of money for users.

        //hack code to make application correctness, override setting by users
        if (isset($data['plan']) && PlanService::isSupportedPromoPlan($data['plan'])) {
            $plan = $this->planRepository->findPlanByKey($data['plan']);
            if ($plan) {
                $data['price'] = $plan->getPrice();
                $data['subscriptionLength'] = $plan->getLength();
            }
        }

        //validate existing code
        if ($numberOfCode == 1) {
            $planCodes = $this->getCouponRepository()->findBy(array('code' => $data['code']), null, 1);
            if ($planCodes && count($planCodes) > 0) {
                throw new \Exception('Code name ' . $data['code'] . ' is already exist', 405);
            }
        }

        for ($i = 0; $i < $numberOfCode; $i++) {
            $randomNumber = '';
            if ($numberOfCode > 1) {
                $randomNumber = str_pad(rand(0, 999), 4, STR_PAD_LEFT);
            }

            //create a new one
            $id = UUID::generate();
            $coupon = new Coupon($id);
            $coupon
                ->setName($data['name'])
                ->setCode('' . $data['code'] . $randomNumber)
                ->setCodeString($data['code'])//keep original one
                ->setMaxRedemption(intval($data['maxRedemption']))
                ->setIsActive((isset($data['isActive']) ? boolval($data['isActive']) : true))
                ->setCurrentUsages(0);

            if (isset($data['price']) && $data['price'] > 0) {
                $coupon->setPrice(doubleval($data['price']));
            }

            if (isset($data['plan'])) {
                $coupon->setPlan($data['plan']);
            }

            if ((!isset($data['plan']) || empty($data['plan'])) &&
                (!isset($data['price']) || doubleval($data['price']) <= 0)
            ) {
                throw new \Exception('Price must be greater than zero.');
            }


            if (isset($data['redemptionStartDate'])) {
                $coupon->setRedemptionStartDate($data['redemptionStartDate']);
            }

            if (isset($data['redemptionEndDate'])) {
                $coupon->setRedemptionEndDate(intval($data['redemptionEndDate']));
            }

            if (isset($data['subscriptionLength'])) {
                $coupon->setSubscriptionLength(intval($data['subscriptionLength']));
            }

            $this->entityManager->persist($coupon);
        }
        $this->entityManager->flush();
        return $coupon; //return the last one
    }
}