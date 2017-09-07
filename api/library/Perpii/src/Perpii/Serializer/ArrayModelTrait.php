<?php

namespace Perpii\Serializer;

trait ArrayModelTrait
{
    private $id;

    private $response;

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function populate()
    {
        return array('id' => $this->getId());
    }

    public function getArrayCopy()
    {
        return array(
            'id'       => $this->getId(),
            'response' => $this->getResponse()
        );
    }

} 