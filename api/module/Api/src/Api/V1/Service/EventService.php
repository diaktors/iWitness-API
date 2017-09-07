<?php

namespace Api\V1\Service;

use Api\V1\Entity\Event;
use Api\V1\Service\Config\AssetConfig;
use Api\V1\Service\Config\EventConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Exception;
use FFMpeg\FFMpeg;
use Perpii\FFMpeg\VideoConcatenation;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;
use ZF\Rest\Exception\CreationException;

class EventService extends ServiceAbstract implements BackgroundProcessInterface
{
    const ENTITY_CLASS = 'Api\V1\Entity\Event';

    /** @var EventConfig */
    private $eventConfig = null;

    /** @var AssetConfig */
    private $assetConfig = null;

    /**
     * @var \Api\V1\Repository\AssetRepository
     */
    private $assetRepository = null;

    /** @var \Api\V1\Repository\EventRepository */
    private $eventRepository = null;

    /** @var EntityRepository */
    private $userRepository = null;

    /** @var FFMpeg */
    private $ffmpeg = null;

    /**
     * @param EventConfig     $eventConfig
     * @param AssetConfig     $assetConfig
     * @param EntityManager   $entityManager
     * @param FFMpeg          $ffmpeg
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventConfig $eventConfig,
        AssetConfig $assetConfig,
        EntityManager $entityManager,
        FFMpeg $ffmpeg,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->eventConfig = $eventConfig;
        $this->assetConfig = $assetConfig;
        $this->ffmpeg      = $ffmpeg;

        $this->eventRepository = $this->entityManager->getRepository('Api\V1\Entity\Event');
        $this->assetRepository = $this->entityManager->getRepository('Api\V1\Entity\Asset');
        $this->userRepository  = $entityManager->getRepository('Api\V1\Entity\User');
    }

    /**
     * Create event if event id is null
     *
     * @param null $eventId
     * @param      $user
     *
     * @return integer
     */
    public function createEventIfIdNull($eventId = null, $lat = null, $lng = null,$user)
    {
        if (empty($eventId)) {
            return $this->createEvent(null, $user);
        }

        return $this->createEvent(array('id' => $eventId, 'initial_lat' => $lat, 'initial_long' => $lng), $user);
    }

    /**
     * @param $data
     * @param $user
     *
     * @return Event
     */
    public function createEvent($data, $user)
    {
        if (!$data) {
            $data = array();
        }
        $eventId = !empty($data['id']) ? trim($data['id']) : UUID::generate();
        $event   = $this->eventRepository->find($eventId);

        if (!$event) {
            $event = new Event($eventId);
            $event->setUser($user);
            $event->setInitialLat($data['initial_lat']);
            $event->setInitialLong($data['initial_long']);
            $this->hydrator->hydrate($data, $event);
            $this->entityManager->persist($event);

            $event->setProcessed(false);
            // flush all data to database
            $this->entityManager->flush();
        }

        return $event;
    }

    /**
     * @param mixed $eventId
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \ZF\Rest\Exception\CreationException
     * @throws \Exception
     * @return bool|void
     */
    public function delete($idOrEntity)
    {
        $this->debug('Begin to delete Event id ');

        /** @var Event $event */
        $event = $this->getEntity($idOrEntity);
        if (!$event) {
            throw new EntityNotFoundException("Could not found Event by id " . $idOrEntity);
        }
        $eventId = $event->getId();

        //delete event
        $eventTrashDir = $this->eventConfig->getEventTrashDir($eventId);

        if (!file_exists($eventTrashDir) && !@mkdir($eventTrashDir, 0777, true)) {
            throw new CreationException('Could not create folder ' . $eventTrashDir, 500);
        }

        $eventCacheDir = $this->eventConfig->getEventCacheDir($eventId);

        if (!@rename($eventCacheDir, $eventTrashDir)) { //move file to trash
            $this->error('Could not rename file or folder ' . $eventCacheDir . ' to ' . $eventTrashDir);
        }

        //delete asset
        foreach ($event->getAssets() as $asset) {
            try {
                /** @var \Api\V1\Entity\Asset $asset */
                $cacheDir = $this->assetConfig->getCacheDir($asset->getId());
                $trashDir = $this->assetConfig->getTrashDir($asset->getId());

                if (!@rename($cacheDir, $trashDir)) { //move file to trash
                    $this->error('Could not rename file or folder ' . $cacheDir . ' to ' . $trashDir);
                }
                $asset->setDeleted(time());

            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }
        }

        //soft delete
        $event->setDeleted(time());
        $this->entityManager->flush();
        $this->debug('End of delete Event id ' . $eventId);

        return true;
    }

    /**
     * @param      $eventId
     * @param bool $force
     *
     * @return bool
     * @throws Exception
     * @throws EntityNotFoundException
     */
    public function  process($eventId, $force = true)
    {
        /** @var Event $event */
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            throw new EntityNotFoundException("Could not found Event by id " . $eventId);
        }

        if ($event->getProcessed() && !$force) {
            throw new Exception("Event '$eventId' is already processed");
        }

        $chunks  = array();
        $preview = null;
        $assets  = $this->fetchForMerging($event->getId());

        if (count($assets) > 0) {
            /** @var \Api\V1\Entity\Asset $asset */
            $asset = $assets[0];
            $event->setInitialLat($asset->getLat());
            $event->setInitialLong($asset->getLng());
        }
		
        foreach ($assets as $asset) {
            /** @var \Api\V1\Entity\Asset $asset */

            if (!$asset->getProcessed()) {
                $this->error("Skipping event {$eventId} while merging video chunks of event {$asset->getId()}: asset is not marked as processed");
                continue;
            }
            if (!in_array($asset->getMediaType(), array('video/quicktime', 'video/mp4', 'video/3gpp'))) {
                $this->error("MediaType = " . $asset->getMediaType() . " Media type != video/quicktime && Media type != video/mp4 && Media type != video/3gpp");
                continue;
			}
			
            /*if (!file_exists($chunk = $this->assetConfig->getAssetLocalCachePath($asset, 'mp4', false))) {
                $this->error("Skipping asset {$asset->getId()} while merging video chunks of event {$eventId}: missing local chunk file, although asset is marked as processed");
                continue;
			}*/
			//start Updated code
			$chunk = $this->assetConfig->getAssetLocalCachePath($asset, 'mp4', false);
            if (!$chunk)
				continue;
			//End Updated Code

            $chunks[] = $chunk; //Escape a string to be used as a shell argument

            // also use first available asset preview image as event preview
            if (!$preview && file_exists($tmp = $this->assetConfig->getAssetLocalCachePath($asset, 'jpg', false))) {
                $preview = $tmp;
            }
        }

        // See http://ffmpeg.org/faq.html#How-can-I-join-video-files_003f
        // to understand merging approach
        if (empty($chunks)) {
            throw new Exception("Couldn't merge chunks of video for event '$eventId': no valid chunks");
        }

        $this->debug("chunks!" . print_r($chunks, true));

        //merge media files
        $mpg = $this->eventConfig->getLocalEventPath($event, 'mp4');

        $concatenation = new VideoConcatenation($chunks, $this->ffmpeg->getFFMpegDriver(), $this->ffmpeg->getFFProbe());
        $concatenation->save($mpg);

        //copy thumbnail from asset folder
        if (!file_exists($mpg) || filesize($mpg) == 0) {
            throw new Exception("Couldn't merge chunks of video for event '$eventId': mpg concatenation failed");
        }

        AssetService::strictChmod($mpg, 0666, 2);

        if ($preview) {
            $jpg = $this->eventConfig->getLocalEventPath($event, 'jpg');
            $this->debug('copy jpeg from ' . $preview);
            copy($preview, $jpg);
        }

		AssetService::strictChmod($jpg, 0666, 2);
        $this->debug("Mpeg Path: " . $mpg);
		$cmd = "ffmpeg -i " . $mpg . " 2>&1";
		preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time);
		exec($cmd);
		if(!empty($time))
				$time = $time[2].":".$time[3].":".$time[4];
		else
				$time =0;
        $this->debug("duration" . $time);
        $event->setDuration($time);

        $event->setProcessed(true);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param $max
     *
     * @return int mixed
     */
    public function fetchForProcessing($max)
    {
        return $this->eventRepository->fetchForProcessing($max);
    }

    /**
     * @param      $id
     * @param null $message
     *
     * @return mixed
     */
    public function markProcessingError($id, $message = null)
    {
        $event = $this->eventRepository->find($id);

        if (($event) && !$event->getProcessed()) {
            $event->increaseAttempted();
            $event->addLog($message);
            if ($event->getAttempted() > $this->eventConfig->maxAttempted()) {
                $event->setFlags(Event::FAILURE);
                $event->setProcessed(true);
            }
            $this->entityManager->flush();
        }
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @return mixed
     */
    public function markProcessingSuccess($id)
    {
        $this->debug('Mark event ' . $id . ' as Success ');

        /** @var \Api\V1\Entity\Event $event */
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw new EntityNotFoundException('Could not found Event id ' . $id);
        }

        $event->setFlags(Event::SUCCESS);
        $event->setProcessed(true);
        $this->entityManager->flush();
    }

    /**
     * @param $eventId
     *
     * @return array
     */
    public function fetchForMerging($eventId)
    {
        return $this->assetRepository->fetchForMerging($eventId);
    }

    /**
     * @param $userId string
     *
     * @return array
     */
    public function fetchForUser($userId)
    {
        return $this->eventRepository->fetchForUser($userId);
    }

    /**
     * todo: should be removed user service, not responsibility of event service
     *
     * @param $userId
     *
     * @return \Api\V1\Entity\User
     */
    public function findUser($userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * Get Event with eager loading for assets
     *
     * @param $id
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @return Event
     */
    public function getEvent($id)
    {
        $event = $this->eventRepository->fetchWithAsset($id);
        if (!$event) {
            throw new EntityNotFoundException("Could not found Event by id " . $id);
        }
        return $event;
    }
}
