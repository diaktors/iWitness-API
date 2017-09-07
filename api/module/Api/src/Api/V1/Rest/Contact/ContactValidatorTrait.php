<?php

namespace Api\V1\Rest\Contact;

use Perpii\InputFilter\InputFilterTrait;
use Perpii\InputFilter\Validator\NoObjectExists;
use ZF\ApiProblem\ApiProblem;

trait ContactValidatorTrait
{
    use InputFilterTrait;

    /**
     * Get input filters by condition
     *
     * @param  mixed $data
     * @param  \Api\V1\Entity\Contact $contact
     * @param  boolean @isCreated
     * @return \Zend\InputFilter\InputFilter
     */
    protected function getInputFilters($data, $contact = null, $isCreated = true)
    {
        $inputFilter = $this->getCommonInputFilter();

        $inputFilter
            ->add(array('name' => 'userId', 'required' => true, 'allow_empty' => false))
            ->add(array('name'       => 'email', 'required' => $isCreated, 'allow_empty' => true,
                        'validators' => array(
                            array('name' => 'Zend\\Validator\\EmailAddress'),
                            array(
                                'name'    => 'Perpii\\InputFilter\\Validator\\NoObjectExists',
                                'options' => array(
                                    'object_repository' => $this->getContactService()->getContactRepository(),
                                    'user_id'           => isset($data['userId']) ? $data['userId'] : null,
                                    'fields'            => array('email'),
                                    'ignore_id'         => $contact ? $contact->getId() : null,
                                    'soft_deleted'      => true,
                                    'messages'          => array(
                                        NoObjectExists::ERROR_OBJECT_FOUND => 'Contact with the same email address '
                                            . $data['email'] . ' exists already'
                                    )
                                ),
                            ),
                        ),
            ))
            ->add(array('name'       => 'phone', 'required' => $isCreated, 'allow_empty' => false,
                        'filters'    => array(
                            array(
                                'name' => 'Perpii\\InputFilter\\Filter\\NormalizePhoneFilter'
                            ),
                        ),
                        'validators' => array(
                            /*
                            array(
                                'name'    => 'Zend\\I18n\\Validator\\PhoneNumber',
                                'options' => array('country' => 'US'),
                            ),
                            */
                            array(
                                'name'    => 'Perpii\\InputFilter\\Validator\\NoObjectExists',
                                'options' => array(
                                    'object_repository' => $this->getContactService()->getContactRepository(),
                                    'user_id'           => isset($data['userId']) ? $data['userId'] : null,
                                    'fields'            => array('phone'),
                                    'ignore_id'         => $contact ? $contact->getId() : null,
                                    'soft_deleted'      => true,
                                    'messages'          => array(
                                        NoObjectExists::ERROR_OBJECT_FOUND => 'Contact with the same phone number '
                                            . $data['phone'] . ' exists already'
                                    )
                                ),
                            ),
                        ),
            ));

        return $inputFilter;
    }

    protected function getCommonInputFilter()
    {
        $inputFilter = $this->getDefaultInputFilter();

        $inputFilter
            ->add(array('name'       => 'phoneAlt', 'required' => false, 'allow_empty' => true,
                        'validators' => array(
                            array(
                                'name'    => 'Zend\\I18n\\Validator\\PhoneNumber',
                                'options' => array('country' => 'US'),
                            ),
                        ),))
            ->add(array('name' => 'firstName', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'lastName', 'required' => false, 'allow_empty' => true))
            ->add(array('name' => 'relationType', 'required' => false, 'allow_empty' => true));

        return $inputFilter;
    }
}
