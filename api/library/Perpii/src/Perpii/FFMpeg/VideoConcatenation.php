<?php

namespace Perpii\FFMpeg;

use FFMpeg\Driver\FFMpegDriver;
use FFMpeg\FFProbe;
use FFMpeg\Media\MediaTypeInterface;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use FFMpeg\Exception\RuntimeException;
use  Neutron\TemporaryFilesystem\Manager as FsManager;

class VideoConcatenation
{
    /** @var array */
    protected $pathfiles;

    /** @var FFMpegDriver */
    protected $driver;

    /** @var FFProbe */
    protected $ffprobe;


    public function __construct(array $pathfiles, FFMpegDriver $driver, FFProbe $ffprobe)
    {
        $this->pathfiles = $pathfiles;
        $this->driver = $driver;
        $this->ffprobe = $ffprobe;

        $this->validatePathFiles();
    }

    /**
     * @throws FileNotFoundException
     */
    private function validatePathFiles()
    {
        $errors = array();
        foreach ($this->pathfiles as $pathfile) {
            if (!file_exists($pathfile)) {
                $errors[] = $pathfile;
            }
        }
        if (count($errors) > 0) {
            throw new FileNotFoundException(null, 0, null, $errors);
        }
    }

    /**
     * @param string $pathFile
     * @throws FileNotFoundException
     */
    public function addFilePath($pathFile)
    {
        if (!file_exists($pathFile)) {
            throw new FileNotFoundException(null, 0, null, array($pathFile));
        }
        $this->pathfiles[] = $pathFile;
    }

    /**
     * @return FFMpegDriver
     */
    public function getFFMpegDriver()
    {
        return $this->driver;
    }

    /**
     * @param FFMpegDriver $driver
     *
     * @return MediaTypeInterface
     */
    public function setFFMpegDriver(FFMpegDriver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return FFProbe
     */
    public function getFFProbe()
    {
        return $this->ffprobe;
    }

    /**
     * @param FFProbe $ffprobe
     *
     * @return MediaTypeInterface
     */
    public function setFFProbe(FFProbe $ffprobe)
    {
        $this->ffprobe = $ffprobe;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathfiles()
    {
        return $this->pathfiles;
    }

    /**
     * @param $outputPathfile
     * @param ListenerInterface|array $listeners A listener or an array of listener to register for this unique run
     * @throws Exception
     * @throws \FFMpeg\Exception\RuntimeException
     * @internal param string $outputFilePath
     * @return $this
     */
    public function save($outputPathfile, $listeners = null)
	{
		/*if (count($this->pathfiles) == 1) 
            return $this->concatFilter($outputPathfile, $listeners);
	    else 
			return $this->concatDemuxer($outputPathfile, $listeners);*/
        return $this->concatFilter($outputPathfile, $listeners);
    }


    /**
     * @param $outputPathfile
     * @param ListenerInterface|array $listeners A listener or an array of listener to register for this unique run
     * @throws Exception
     * @throws \FFMpeg\Exception\RuntimeException
     * @internal param string $outputFilePath
     * @return $this
     */
    public function concatDemuxer($outputPathfile, $listeners = null)
    {
        if (count($this->pathfiles) <= 0) {
            return $this;
        }
        //create temporary file
        $fs = FsManager::create();
        $file = $fs->createTemporaryFile('concate_', null, 'txt');

        $fileHandle = fopen($file, 'w');
        if (false === $fileHandle) {
            throw new Exception('Could not create temporary file ' . $file);
        }

        foreach ($this->pathfiles as $path) {
            fwrite($fileHandle, 'file ' . $path . "\n");
        }
        fclose($fileHandle);

        //ffmpeg -y -f concat -i mylist.txt -c copy output

        $commands = array('-y');
        $commands[] = '-f';
        $commands[] = 'concat';
        $commands[] = '-i';
        $commands[] = $file;
        $commands[] = '-c';
        $commands[] = 'copy';
		$commands[] = $outputPathfile;

        try {
            $this->driver->command($commands, false, $listeners);
        } catch (ExecutionFailureException $e) {
            throw new RuntimeException('Encoding failed', $e->getCode(), $e);
        }

        $fs->clean();
        return $this;
    }


    /**
     * @param $outputPathFile
     * @param null $listeners
     * @return $this
     * @throws \FFMpeg\Exception\RuntimeException
     */
    public function concatFilter($outputPathFile, $listeners = null)
	{
        if (count($this->pathfiles) <= 0) {
            return $this;
        }

        //ffmpeg -i 1.mp4 -i 2.mp4 -i 3.mp4 -i 4.mp4 -i 5.mp4 -filter_complex '[0:0][0:1][1:0][1:1][2:0][2:1][3:0][3:1][4:0][4:1]concat=n=5:v=1:a=1 [v] [a]' -map "[v]" -map "[a]" output5.mp4
        $commands = array('-y');
        $i = 0;
		$matrix = '';
        foreach ($this->pathfiles as $path) {
            $commands[] = '-i';
            $commands[] = $path;
            $matrix = $matrix . '[' . $i . ':0][' . $i . ':1]';
            $i++;
        }

        //if there is only 1 chunk, should only encode & move it to $outputPathFile
        if (count($this->pathfiles) > 1) {
            $matrix = $matrix . 'concat=n=' . count($this->pathfiles) . ':v=1:a=1 [v] [a]';
            $commands[] = '-filter_complex';
            $commands[] = $matrix;
            $commands[] = '-map';
            $commands[] = '[v]';
            $commands[] = '-map';
			$commands[] = '[a]';
			
        }

		$commands[] = $outputPathFile;

        try {
            $this->driver->command($commands, false, $listeners);
        } catch (ExecutionFailureException $e) {
            throw new RuntimeException('Encoding failed', $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * @param $outputPathFile
     * @param null $listeners
     * @throws \Exception
     */
    public function concatProtocol($outputPathFile, $listeners = null)
    {
        throw new \Exception('Concatenation protocol was not implemented');
    }

    /**
     * @param $outputPathFile
     * @param null $listeners
     * @throws \Exception
     */
    public function concatUsingExternalScript($outputPathFile, $listeners = null)
    {
        throw new \Exception('Concatenation using external script was not implemented');
	}

}
