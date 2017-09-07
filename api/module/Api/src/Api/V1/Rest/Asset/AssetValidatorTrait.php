<?php

namespace Api\V1\Rest\Asset;

use Perpii\InputFilter\InputFilterTrait;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

trait AssetValidatorTrait
{
    use InputFilterTrait;

    private function validateMediaUploaded($data)
    {
        // Input filter
        $inputFilter = $this->getDefaultInputFilter();

        // File Input
        $fileInput = new FileInput('media');

        $fileInput->setRequired(true);
        $fileInput->setAllowEmpty(false);

        $fileInput
            ->getValidatorChain()
            ->attachByName('filesize', array('max' => $this->config['maxFileSize']))
            ->attachByName('filemimetype', array('mimeType' => implode(',', $this->getMediaTypes())));

        $inputFilter->add($fileInput);

        //validate and filter data
        $inputFilter->setData($data);





		if($data['media']['size'] == 0)
		{



// return new JsonModel(array('status' => '200', 'message' => 'InVALID video'));



			/* return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Failed Validation',
                    null,
                    null,
                    array('validation_messages' => 'Invalid video'
                    )
                )
            );*/
		}


        if (!$inputFilter->isValid()) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Failed Validation',
                    null,
                    null,
                    array('validation_messages' => $inputFilter->getMessages()
                    )
                )
            );
        }
    }

    private function getMediaTypes()
    {
        return array('video/mp4', 'video/quicktime', 'video/mpeg', 'video/x-flv');
    }
}
