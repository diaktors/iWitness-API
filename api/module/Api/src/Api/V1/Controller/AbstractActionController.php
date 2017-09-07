<?php

namespace Api\V1\Controller;

use Zend\Mvc\MvcEvent;
use ZF\Rest\ResourceEvent;

abstract class  AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{

    /**
     * Current identity, if discovered in the resource event.
     *
     * @var \ZF\MvcAuth\Identity\IdentityInterface
     */
    protected $identity;

    /**
     * Get current identity, only not null if you already authenticate with the web app
     *
     * @return null|IdentityInterface
     */
    protected function getIdentity()
    {
        if ($this->identity) {
            return $this->identity;
        }

        $event = $this->getEvent();

        if ($event instanceof ResourceEvent) {
            $this->identity = $event->getIdentity();
            return $this->identity;
        }

        if ($event instanceof MvcEvent) {
            $this->identity = $event->getParam('ZF\MvcAuth\Identity');
            return $this->identity;
        }

        return null;
    }
}
