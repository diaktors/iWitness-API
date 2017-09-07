<?php

namespace Api\V1\Rest\Coupon;

use DoctrineModule\Validator\NoObjectExists;
use DoctrineModule\ValidatorNoObjectExists;
use Herrera\Phar\Update\Exception\Exception;
use Perpii\InputFilter\InputFilterTrait;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class CouponValidatorTrait
 * @package Api\V1\Rest\Coupon
 */
trait CouponValidatorTrait
{
    use InputFilterTrait;

    /**
     * @return \Zend\InputFilter\InputFilter
     */
    private function getCreatingCouponFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            ->add(array('name' => 'numberOfCode', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\Digits'),
                ),))
            ->add(array('name' => 'name', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'code', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array(
                        'name' => 'DoctrineModule\\Validator\\NoObjectExists',
                        'options' => array(
                            'object_repository' => $this->getCouponService()->getCouponRepository(),
                            'fields' => array('codeString'),
                            'soft_deleted'      => true,
                            'messages' => array(
                                NoObjectExists::ERROR_OBJECT_FOUND => 'Coupon with the same Code  existing already'
                            )
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'maxRedemption', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\Digits'),
                ),))
            ->add(array('name' => 'isActive', 'required' => false, 'allow_empty' => true))

            ->add(array('name' => 'price', 'requiredrequired' => false, 'allow_empty' => true,
                'validators' => array(
                    array('name' => 'Zend\\I18n\\Validator\\Float'),
                ),))

            ->add(array('name' => 'subscriptionLength', 'required' => true, 'allow_empty' => true,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\Digits'),
                ),))

            ->add(array('name' => 'plan', 'required' => false, 'allow_empty' => true))

            ->add(array('name' => 'redemptionStartDate', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),
            ))
            ->add(array('name' => 'redemptionEndDate', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int'),
                ),
            ));
        return $inputFilter;
    }
}
