<?php

namespace Api\V1\Rpc\User;

use Api\V1\Security\Authorization\AclAuthorization;
use DoctrineModule\ValidatorNoObjectExists;
use Herrera\Phar\Update\Exception\Exception;
use Perpii\InputFilter\InputFilterTrait;
use Zend\InputFilter\Factory;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;


trait UserValidatorTrait
{
    use InputFilterTrait;

    private function validateUploadPhoto($user, $data, $fileName)
    {
        $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_UPDATE);
        if ($result !== true) {
            return $result;
        }

        if (!isset($this->photoService->config['baseDir'])) {
            return new ApiProblem(417, 'Missing path to user avatar folder in configuration ');
        }

        $fileInput = new FileInput($fileName);
        $fileInput->setRequired(true);
        $fileInput->setAllowEmpty(false);
        $fileInput
            ->getValidatorChain()
            ->attachByName('filesize', array('max' => 12897152)) //6MB
            ->attachByName('filemimetype', array('mimeType' => 'image/png,image/x-png,image/jpg,image/jpeg,image/gif'));
            //->attachByName('fileimagesize', array('maxWidth' => 1024, 'maxHeight' => 1024));

        $inputFilter = $this->getDefaultInputFilter();
        $inputFilter->add($fileInput);
        $inputFilter->setData($data); //validate

        if (!$inputFilter->isValid()) {
                return new ApiProblem(422, 'Failed Validation', null, null,
                    array('validation_messages' => $inputFilter->getMessages()
                    )
                );
        }

        return true;
    }
}