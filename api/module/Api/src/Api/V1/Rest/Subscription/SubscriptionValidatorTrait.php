<?php
namespace Api\V1\Rest\Subscription;

use Api\V1\Entity\GiftCard;
use Api\V1\Service\PlanService;
use Perpii\InputFilter\InputFilterTrait;
use Zend\Validator\InArray;
use ZF\ApiProblem\ApiProblem;

trait SubscriptionValidatorTrait
{
    use InputFilterTrait;

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    private function getFreeInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'userId  ', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'originalPhone', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'originalPhoneModel', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'customerIp', 'required' => false, 'allow_empty' => false));

        return $inputFilter;
    }

    private function getReAccessSubscriptionInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'receiptId', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'originalreceiptid', 'required' => false, 'allow_empty' => true));

        return $inputFilter;
    }

    /**
     * @return array|\Zend\InputFilter\InputFilter
     */
    private function getPaidInputFilter()
    {
        $inputFilter = $this->getPaymentInputFilter();
        $inputFilter
            ->add(array('name' => 'originalPhoneModel', 'required' => true, 'allow_empty' => false));
        return $inputFilter;
    }

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function getGiftCardInputFilter()
    {
        $inputFilter = $this->getPaymentInputFilter();

        $inputFilter
            ->add(array('name' => 'senderName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'existingUser', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'senderEmail', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\Validator\EmailAddress'),
                ),
            ))
            ->add(array('name' => 'recipients', 'required' => true, 'allow_empty' => false));

        return $inputFilter;
	}

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function getFreeGiftCardInputFilter()
    {
        $inputFilter = $this->getNoPaymentInputFilter();

        $inputFilter
            ->add(array('name' => 'senderName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'existingUser', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'senderEmail', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\Validator\EmailAddress'),
                ),
            ))
            ->add(array('name' => 'recipients', 'required' => true, 'allow_empty' => false));

        return $inputFilter;
    }

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function getRecipientInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();

        $inputFilter
            ->add(array('name' => 'name', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'email', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\Validator\EmailAddress'),
                ),
            ))
            ->add(array('name' => 'message', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'deliveryDate', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),
            ));
        return $inputFilter;
	}

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function getNoPaymentInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'userId  ', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'plan', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'originalPhone', 'required' => false, 'allow_empty' => true,
            //->add(array('name' => 'originalPhone', 'required' => true, 'allow_empty' => false,
                    'filters' => array(
                        array(
                            'name' => 'Zend\\Filter\\Digits'
                        ),
                    )
                )
            )
            ->add(array('name' => 'firstName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'lastName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'address1', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'address2', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'city', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'state', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'zip', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'country', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'customerIp', 'required' => false, 'allow_empty' => false));

		return $inputFilter;
	}

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function getPaymentInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'userId  ', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'plan', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'originalPhone', 'required' => false, 'allow_empty' => true,
            //->add(array('name' => 'originalPhone', 'required' => true, 'allow_empty' => false,
                    'filters' => array(
                        array(
                            'name' => 'Zend\\Filter\\Digits'
                        ),
                    )
                )
            )
            ->add(array('name' => 'cardType', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array(
                        'name' => 'Zend\Validator\InArray',
                        'options' => array(
                            'haystack' => array('mastercard', 'visa', 'amex', 'discover', 'jcb', 'visa_electron'),
                            'strict' => InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY,
                            'messages' => array('notInArray' => 'Your card type does not support by application, currently it supports: MasterCard, Visa, American Express, Discover, JCB, Visa Electron'),
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'cardNum', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\Validator\CreditCard'),
                ),
            ))
            ->add(array('name' => 'expMonth', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),
            ))
            ->add(array('name' => 'expYear', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),
            ))
            ->add(array('name' => 'cardCode', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array(
                        'name' => 'Zend\Validator\Regex',
                        'options' => array(
                            'pattern' => '/^[0-9]{3,4}$/',
                        )
                    ),
                ),))
            ->add(array('name' => 'firstName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'lastName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'address1', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'address2', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'city', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'state', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'zip', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'country', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'customerIp', 'required' => false, 'allow_empty' => false));

        return $inputFilter;
    }

    private function getInAppInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'receiptId', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'originalreceiptid', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'userId', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'receiptDate', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'packageName', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'isRenew  ', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'week_test', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'originalPhone', 'required' => false, 'allow_empty' => false,
                    'filters' => array(
                        array('name' => 'Zend\\Filter\\Digits'),
                    )
                )
            )
            ->add(array('name' => 'originalPhoneModel', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'firstName', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'lastName', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'address1', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'address2', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'city', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'state', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'zip', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'country', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'customerIp', 'required' => false, 'allow_empty' => false));
        return $inputFilter;
    }

    public function getAppleInAppInputFilter()
    {
        $inputFilter = $this->getInAppInputFilter();
        $inputFilter
            ->add(array('name' => 'productId', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'receiptData', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'sandboxtest', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'originalreceiptid', 'required' => false, 'allow_empty' => true));

        return $inputFilter;
    }

    public function getGoogleInAppInputFilter()
    {
        $inputFilter = $this->getInAppInputFilter();
        $inputFilter
            ->add(array('name' => 'signature', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'signedData', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'purchasedToken', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'productId', 'required' => false, 'allow_empty' => false))
            ->add(array('name' => 'appName', 'required' => true, 'allow_empty' => false));

        return $inputFilter;
    }

    /**
     * @param $data
     * @internal param $promoCode
     * @internal param $data
     * @return \Api\V1\Entity\Coupon|null|ApiProblem
     */
    private function validateCoupon($data)
    {
        $coupon = null;
        if (isset($data['promoCode'])) {
            /** @var \Api\V1\Entity\Coupon $coupon */
            $coupon = $this
                ->subscriptionService
                ->getCoupon(array('code' => $data['promoCode']));

            if (!$coupon) {
                return new ApiProblem(404, 'Promo code is invalid');
            }

            if (!$coupon->getIsActive()) {
                return new ApiProblem(406, 'Promo has been disable');
            }

            if ($coupon->getCurrentUsages() >= $coupon->getMaxRedemption()) {
                return new ApiProblem(406, 'Promo has reached its max usages ');
            }

            //check redemption_start_date
            $time = time();
            $redemptionStart = $coupon->getRedemptionStartDate();
            if ($redemptionStart > 0 && $time < $redemptionStart) {
                return new ApiProblem(406, 'Invalid promo');
            }

            //check end date
            $redemptionEnd = $coupon->getRedemptionEndDate();
            if ($redemptionEnd > 0 && $time > $redemptionEnd) {
                return new ApiProblem(406, 'Promo has expired');
            }

            //check plan matching if any
            if (isset($data['plan']) && $data['plan'] != PlanService::ANY_PLAN && !$coupon->isValidPlan($data['plan'])) {
                return new ApiProblem(406, 'The plan you chose does not match the coupon');
            }
        }

        return $coupon;
    }
} 
