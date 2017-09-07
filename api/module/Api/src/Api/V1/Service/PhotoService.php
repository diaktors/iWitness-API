<?php
namespace Api\V1\Service;

use Aws\Common\Facade\S3;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use Exception;
use Imagick;
use ImagickException;
use Perpii\FFMpeg\FileNotFoundException;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\Rest\Exception\CreationException;


class PhotoService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\User';

    /** @var  array */
    public $config;

    /** @var \Doctrine\ORM\EntityRepository */
    protected $userRepository;

    /** @var  \Api\V1\Hydrator\UserHydrator */
    protected $userHydrator;

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @param array $config
     */
    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        array $config)
    {
        parent::__construct($entityManager, $logger);
        $this->userRepository = $entityManager->getRepository('Api\V1\Entity\User');
        $this->config         = $config;
    }


    /**
     * @param \Api\V1\Entity\User $user
     * @param $file
     * @throws \Exception
     */
    public function uploadPhoto($user, $file)
    {
        $baseDir    = $this->config['baseDir'];
        $targetName = $user->getPhoto() ? $user->getPhoto() : UUID::generate() . '.jpg';
        $targetDir  = $baseDir . '/' . $targetName;

        if (!file_exists($baseDir)) {
            if (!@mkdir(dirname($baseDir), 0777, true)) {
                throw new CreationException('The folder ' . dirname($baseDir) . ' does not exists', 500);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $targetDir)) {
            throw new \Exception('Could not save upload file to ' . $targetDir, 500);
        }

        $user->setPhoto($targetName);
        $this->entityManager->flush();

        $this->resizeAndCachePhoto($targetName, null, true);
    }

    /**
     * @param $userId
     * @param $photoName
     * @param $size
     * @return string
     */
    public function validatePhoto($userId, $photoName, $size)
    {
        if ($userId) {
            /** @var \Api\V1\Entity\User $user */
            $user = $this->userRepository->find($userId);

            if ($user) {
                $photoName = $user->getPhoto();
            }
        }

        if (!$photoName) {
            new ApiProblem(404, 'The requested photo is not found');
        }

        if (!isset($this->config['size'][$size])) {
            new ApiProblem(404, 'The requested size ' . $size . ' is not found');
        }

        return $photoName;
    }

    /**
     * @param $name
     * @param null $original
     * @param bool $forceResize
     * @throws \ZF\Rest\Exception\CreationException
     * @throws \Perpii\FFMpeg\FileNotFoundException
     */
    public function resizeAndCachePhoto($name, $original = null, $forceResize = false)
    {
        $baseDir  = $this->config['baseDir'];
        $cacheDir = $this->config['cacheDir'];
        $sizes    = $this->config['size'];

        if (!$original) {
            //this is usually s3 account
            $original = $baseDir . '/' . $name;
        }

        $imagePath = null;
        if (file_exists($original)) {
            $imagePath = $original;
        } else if (file_exists(realpath($original))) {
            //config file pointed to local storage
            $imagePath = realpath($original);
        } else {
            throw new FileNotFoundException('The requested file ' . $original . ' is not found', 404);
        }

        $img = new Imagick($imagePath);

        $cacheDirExists = file_exists($cacheDir);
        if (!$cacheDirExists || $forceResize) {
            if (!$cacheDirExists && !@mkdir($cacheDir, 0777, true)) {
                throw new CreationException('Could not create folder ' . $cacheDir);
            }

            $cacheFile = $cacheDir . '/' . $name;
            if (!@touch($cacheFile)) {//create empty file
                throw new CreationException('Could not create file ' . $cacheFile);
            };
            stream_copy_to_stream(fopen($imagePath, 'r'), fopen($cacheFile, 'w+'));
        }

        foreach ($sizes as $size => $details) {
            $this->resizePhoto($img, $size, $details, $cacheDir, $name, $forceResize);
        }
    }

    /**
     * @param Imagick $img
     * @param string $size
     * @param array $config
     * @param string $destinationDir
     * @param string $photoName
     * @param bool $forceResize
     * @throws \Exception
     */
    private function resizePhoto(
        Imagick $img,
        $size,
        array $config,
        $destinationDir,
        $photoName,
        $forceResize = false)
    {
        $resizeDir  = $destinationDir . '/' . $size;
        $resizeName = $resizeDir . '/' . $photoName;
        if (!file_exists($resizeName) || $forceResize) {

            if (!file_exists($resizeDir) && !@mkdir($resizeDir, 0777, true)) {
                throw new CreationException('Could not create folder ' . $resizeDir);
            }

            if (!is_writable($resizeDir)) {
                throw new Exception('Could not write image to ' . $resizeDir, 500);
            }

            $img->setCompression(Imagick::COMPRESSION_JPEG);
            $img->setCompressionQuality($config['quality']);
            $img->setImageFormat('jpg');
            $img->thumbnailImage($config['width'], null);
            $img->writeImage($resizeName);
        }
    }
}