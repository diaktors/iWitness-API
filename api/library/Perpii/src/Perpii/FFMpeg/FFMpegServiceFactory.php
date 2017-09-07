<?php

namespace Perpii\FFMpeg;

use Perpii\Log\LogManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Cache\ArrayCache;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Zend\ServiceManager\FactoryInterface;

class FFMpegServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $config = $serviceLocator->get('config');
        $ffmpegConfig = $config['ffmpeg']['default']['configuration']['ffmpeg'];
        $ffprobeConfig = $config['ffmpeg']['default']['configuration']['ffprobe'];

        $ffmpegDefaultConfig = array(
            'ffmpeg.threads' => $ffmpegConfig['threads'],
            'ffmpeg.timeout' => $ffmpegConfig['timeout'],
            'ffmpeg.binaries' => $ffmpegConfig['binaries'],
            'ffprobe.timeout' => $ffprobeConfig['timeout'],
            'ffprobe.binaries' => $ffprobeConfig['binaries'],
        );

        // set timeout for ffprobe instance
        $ffmpegDefaultConfig['timeout'] = $ffmpegDefaultConfig['ffprobe.timeout'];
        $ffprobe = FFProbe::create($ffmpegDefaultConfig, $logger, new ArrayCache());

        // overwrite timeout for ffmpeg
        $ffmpegDefaultConfig['timeout'] = $ffmpegDefaultConfig['ffmpeg.timeout'];

        return FFMpeg::create($ffmpegDefaultConfig, $logger, $ffprobe);
    }
} 