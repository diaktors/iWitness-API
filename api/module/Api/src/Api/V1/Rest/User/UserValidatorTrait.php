<?php

namespace Api\V1\Rest\User;

use DoctrineModule\Validator\NoObjectExists;
use DoctrineModule\ValidatorNoObjectExists;
use Herrera\Phar\Update\Exception\Exception;
use Perpii\InputFilter\InputFilterTrait;
use Perpii\Util\Cryptography;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;


trait  UserValidatorTrait
{
    use InputFilterTrait;

    /**
     * @param $subscriber
     * @param $data
     * @return bool|ApiProblem
     */
    private function validatePasswordChange($subscriber, $data)
    {
        if (isset($data['newPassword'])) {
            if (!isset($data['password'])) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['password' => 'Old password is require when changing password'],
                ));
            }
            if (!Cryptography::verify($data['password'], $subscriber->getPassword())) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['password' => 'Old password does not match'],
                ));
            }
        }

        return true;
    }


    private function validatePhoneChange($subscriber, $data)
    {
        if (isset($data['phone'])) {
            if (!isset($data['phonePassword'])) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['phonePassword' => 'Password is require when changing phone'],
                ));
            }
            if (!Cryptography::verify($data['phonePassword'], $subscriber->getPassword())) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['phonePassword' => 'Password does not match'],
                ));
            }
        }

        return true;
    }

    /**
     * @param $data
     * @return \Zend\InputFilter\InputFilter
     */
    private function getCreatingInputFilter($data)
    {
        $inputFilter = $this->getCommonInputFilter();
        $inputFilter
            ->add(array('name' => 'email', 'required' => true, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\EmailAddress'),
                    array(
                        'name' => 'Perpii\\InputFilter\\Validator\\NoObjectExists',
                        'options' => array(
                            'object_repository' => $this->getUserService()->getUserRepository(),
                            'fields' => array('email'),
                            'soft_deleted'  => true,
                            'messages' => array(
                                NoObjectExists::ERROR_OBJECT_FOUND => 'User with the same email '
                                    . (isset($data['email']) ? $data['email'] : '') . ' exists already.'
                            )
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'phone', 'required' => true, 'allow_empty' => false,
                'filters' => array(
                    array(
                        'name' => 'Perpii\\InputFilter\\Filter\\NormalizePhoneFilter'
                    ),
                ),
                'validators' => array(
                    /*
                    array(
                        'name' => 'Zend\I18n\Validator\PhoneNumber',
                        'options' => array('country' => 'US'),
                    ),
                    */
                    array(
                        'name' => 'DoctrineModule\\Validator\\NoObjectExists',
                        'options' => array(
                            'object_repository' => $this->getUserService()->getUserRepository(),
                            'fields' => array('phone'),
                            'soft_deleted' => true,
                            'messages' => array(
                                NoObjectExists::ERROR_OBJECT_FOUND => 'User with the same phone number '
                                    . (isset($data['phone']) ? $data['phone'] : '') . ' exists already.'
                            )
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'password', 'required' => true, 'allow_empty' => false))
            ->add(array('name' => 'subscriptionUuid', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Perpii\\InputFilter\\Validator\\UUID'),
                ),
            ));

        return $inputFilter;

    }

    /**
     * @param $id
     * @param $data
     * @return \Zend\InputFilter\InputFilter
     */
    private function getUpdatingInputFilter($id, $data)
    {

        $inputFilter = $this->getCommonInputFilter();
        $inputFilter
            ->add(array('name' => 'email', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Zend\\Validator\\EmailAddress'),
                    array(
                        'name' => 'Perpii\\InputFilter\\Validator\\NoObjectExists',
                        'options' => array(
                            'object_repository' => $this->getUserService()->getUserRepository(),
                            'fields' => array('email'),
                            'soft_deleted'      => true,
                            'ignore_id' => $id,
                            'messages' => array(
                                NoObjectExists::ERROR_OBJECT_FOUND => 'User with the same email '
                                    . (isset($data['email']) ? $data['email'] : '') . ' exist already'
                            )
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'phone', 'required' => false, 'allow_empty' => false,
                'filters' => array(
                    array(
                        'name' => 'Perpii\\InputFilter\\Filter\\NormalizePhoneFilter'
                    ),
                ),
                'validators' => array(

                    /*
                    array(
                        'name' => 'Zend\I18n\Validator\PhoneNumber',
                        'options' => array('country' => 'US'),
                    ),
                    */
                    array(
                        'name' => 'Perpii\\InputFilter\\Validator\\NoObjectExists',
                        'options' => array(
                            'object_repository' => $this->getUserService()->getUserRepository(),
                            'fields' => array('phone'),
                            'ignore_id' => $id,
                            'soft_deleted'      => true,
                            'messages' => array(
                                NoObjectExists::ERROR_OBJECT_FOUND => 'User with the same phone number '
                                    . (isset($data['phone']) ? $data['phone'] : '') . ' exist already'
                            )
                        ),
                    ),
                ),
            ))
            ->add(array('name' => 'subscriptionUuid', 'required' => false, 'allow_empty' => false,
                'validators' => array(
                    array('name' => 'Perpii\\InputFilter\\Validator\\UUID'),
                ),
            ))
            ->add(array('name' => 'suspended', 'required' => false, 'allow_empty' => false,
            ));


        if (isset($data['password'])) {
            $inputFilter->add(array(
                    'name' => 'newPassword',
                    'required' => true,
                    'allow_empty' => false,
                    'error_message' => 'New password is required and can\'t be empty')
            );
        }

        if (isset($data['newPassword'])) {
            $inputFilter->add(array(
                    'name' => 'password',
                    'required' => true,
                    'allow_empty' => false,
                    'error_message' => 'Password is required and can\'t be empty')
            );
        }

        return $inputFilter;
    }


    /**
     * @return \Zend\InputFilter\InputFilter
     */
    protected function getCommonInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter
            //phone
            ->add(array('name' => 'phoneAlt', 'required' => false, 'allow_empty' => true,
                'filters' => array(
                    array(
                        'name' => 'Perpii\\InputFilter\\Filter\\NormalizePhoneFilter'
                    ),
                ),
                /*
                'validators' => array(
                    array(
                        'name' => 'Zend\I18n\Validator\PhoneNumber',
                        'options' => array('country' => 'US'),
                    ),
                ),*/
            ))
            ->add(array('name' => 'firstName', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'lastName', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'address1', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'address2', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'city', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'state', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'zip', 'required' => false, 'allow_empty' => true))
            //email
            ->add(array('name' => 'gender', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'ethnicity', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'birthDate', 'required' => false, 'allow_empty' => true,
                'validators' => array(
                    array('name' => 'Zend\I18n\Validator\Int')
                ),
            ))
            ->add(array('name' => 'heightFeet', 'required' => false, 'allow_empty' => true,
                'validators' => array(
                    array(
                        'name' => 'Zend\I18n\Validator\Float',
                    ),
                )))
            ->add(array('name' => 'heightInches', 'required' => false, 'allow_empty' => true,
                'validators' => array(
                    array(
                        'name' => 'Zend\I18n\Validator\Float',
                    ),
                )))
            ->add(array('name' => 'weight', 'required' => false, 'allow_empty' => true,
                'validators' => array(
                    array(
                        'name' => 'Zend\I18n\Validator\Float',
                    ),
                )))
            ->add(array('name' => 'eyeColor', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'hairColor', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'ethnicity', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'distFeature', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'timezone', 'required' => false, 'allow_empty' => true));

        return $inputFilter;
    }
}
