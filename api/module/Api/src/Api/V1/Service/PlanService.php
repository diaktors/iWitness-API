<?php

namespace Api\V1\Service;


use Api\V1\Entity\Plan;

class PlanService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\Plan';
    const IOS_MONTHLY_SUBSCRIPTION = 'com.iwitness.monthly_subscribe';
    const IOS_YEARLY_SUBSCRIPTION = 'com.iwitness.yearly_subscribe';
    const IOS_MONTHLY_SUBSCRIPTION_AR = 'com.iwitness.monthly_subscribe_ar_new';
    const IOS_YEARLY_SUBSCRIPTION_AR = 'com.iwitness.yearly_subscribe_ar_new';
    const ANDROID_MONTHLY_SUBSCRIPTION = 'com.iwitness.android.monthly_sub_2.99';
	const ANDROID_YEARLY_SUBSCRIPTION = 'com.iwitness.android.yearly_sub_29.99';
    const ANDROID_MONTHLY_SUBSCRIPTIONTEST = 'com.iwitness.androidapp.monthly_sub_2.99';
    const ANDROID_YEARLY_SUBSCRIPTIONTEST = 'com.iwitness.androidapp.yearly_sub_29.99';
    const ANDROID_MONTHLY_SUBSCRIPTIONTEST1 = 'com.iwitness.androidtest.monthly_sub_2.99';
    const ANDROID_YEARLY_SUBSCRIPTIONTEST1 = 'com.iwitness.androidtest.yearly_sub_29.99';
    const FREE_PLAN = 'free';
    const MONTH_PLAN = 'month';
    const YEAR_PLAN = 'year';
    const TWO_YEARS_PLAN = '2-years';
    const SAFEKIDYEAR_PLAN = 'safekidyear';
    const SEATTLEYEAR_PLAN = 'seattleyear';
    const YEAR_GIFT_CARD_PLAN = 'giftplanyear';
    const FREE_GIFT_CARD_PLAN = 'freegiftcard';
    const WSPTAYEAR_PLAN = 'wspta';
    const STUDENTYEAR_PLAN = 'student';
    const ANY_PLAN = 'promo';


    /** @var array | null */
    private static $availablePromoPlans = null;

    /**
     * @param $package
     * @return string
     * @throws \Exception
     */
    public static function inAppPackageToPlan($package)
    {
        switch ($package) {
            case PlanService::IOS_MONTHLY_SUBSCRIPTION :
            case PlanService::IOS_MONTHLY_SUBSCRIPTION_AR :
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTION :
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST :
            case PlanService::ANDROID_MONTHLY_SUBSCRIPTIONTEST1 :
                $plan = PlanService::MONTH_PLAN;
                break;
            case PlanService::IOS_YEARLY_SUBSCRIPTION :
            case PlanService::IOS_YEARLY_SUBSCRIPTION_AR :
            case PlanService::ANDROID_YEARLY_SUBSCRIPTION :
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST :
            case PlanService::ANDROID_YEARLY_SUBSCRIPTIONTEST1 :
                $plan = PlanService::YEAR_PLAN;
                break;
            default:
                throw new \Exception(sprintf('Invalid package name "%s"', $package), 406);
                break;
        }
        return $plan;
    }

    /**
     * Get build in plans
     * @return array
     */
    private static function  getAvailablePromoPlans()
    {
        if (self::$availablePromoPlans !== null) {
            return self::$availablePromoPlans;
        }

        self::$availablePromoPlans = array(
            self::FREE_PLAN,
            self::SAFEKIDYEAR_PLAN,
            self::SEATTLEYEAR_PLAN,
            self::YEAR_GIFT_CARD_PLAN,
            self::WSPTAYEAR_PLAN,
            self::STUDENTYEAR_PLAN
        );
        return self::$availablePromoPlans;
    }

    /**
     * @param $plan
     * @return bool
     */
    public static function isSupportedPromoPlan($plan)
    {
        return in_array($plan, PlanService::getAvailablePromoPlans());
    }


    /**
     * @param $key
     * @throws Exception
     * @return null | Plan
     */
    public function findPlanByKey($key)
    {
        $plan = $this->getRepository()->findPlanByKey($key);
        return $plan;
    }

    /**
     * @param Plan $plan
     * @param array $data
     * @return Plan
     */
    public function updatePlan(Plan $plan, array $data)
    {
        $data['modified'] = time();
        return $this->patch($plan, $data);
    }
}
