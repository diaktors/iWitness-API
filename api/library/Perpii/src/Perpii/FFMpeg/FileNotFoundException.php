<?php

namespace Perpii\FFMpeg;


class FileNotFoundException extends \Exception
{
    /** @var array */
    private $paths = array();

    public function __construct($message = null, $code = 0, \Exception $previous = null, array $paths = array())
    {
        $this->paths = $paths;

        if (null === $message) {
            if (count($paths) <= 0) {
                $message = 'File could not be found.';
            } else {
                $message = sprintf('File "%s" could not be found.', implode('|', $paths));
            }
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}