<?php

namespace Api\V1\Rpc\Event;

use Api\V1\Controller\BaseActionController;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Service\Config\EventConfig;
use Api\V1\Service\EventService;
use Api\V1\Service\MediaFileInfo;
use Exception;
use Psr\Log\LoggerInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;

class EventController extends BaseActionController
{
    /** @var array */
    private $sendFileConfig = null;
    /** @var array| EventConfig */
    private $eventConfig = null;
    /** @var EventService $eventService */
    private $eventService = null;

    /**
     * @param array $sendFileConfig
     * @param EventConfig $eventConfig
     * @param EventService $eventService
     * @param AuthenticationServiceInterface|UserService $authentication
     * @param AuthorizationInterface $authorization
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $sendFileConfig,
        EventConfig $eventConfig,
        EventService $eventService,
        AuthenticationServiceInterface $authentication,
        AuthorizationInterface $authorization,
        LoggerInterface $logger)
    {
        parent::__construct($authentication, $authorization, $logger);

        $this->sendFileConfig = $sendFileConfig;
        $this->eventConfig = $eventConfig;
        $this->eventService = $eventService;
    }

    /**
     * Point-out image url of this event
     *
     * @return boolean
     */
    public function imageUrlAction()
    {
        try {
            return $this->mediaRendering(MediaFileInfo::TYPE_JPG, 'image/jpeg');
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Media processing for rendering to client
     *
     * @param $mediaType
     * @param $mediaContentType
     * @return null|\Zend\Http\Response
     */
    private function mediaRendering($mediaType, $mediaContentType)
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $id = $router->getParam('event_id');
            if (empty($id)) {
                throw new Exception('Provide EventId for this action');
            }

            $event = $this->eventService->find($id);
            /** @var MediaFileInfo $mediaInfo */
            $mediaInfo = $this->eventConfig->getLocalEventFileInfo($event, $mediaType, false);
			#$file = "/volumes/log/api/test-log.log";
			#file_put_contents($file, $id, FILE_APPEND | LOCK_EX);
            return $this->sendFile(realpath($mediaInfo->getFilePath()), $mediaContentType, $this->sendFileConfig);

        } catch (Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * Point-out video url of this event
     *
     * @return boolean
     */
    public function videoUrlAction()
    {
        try {
            return $this->mediaRendering(MediaFileInfo::TYPE_MP4, 'video/mp4');
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_EVENT;
    }
} 
