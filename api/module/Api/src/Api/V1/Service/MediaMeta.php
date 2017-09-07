<?php

namespace Api\V1\Service;

use PHPExiftool\Driver\Metadata\Metadata;
use PHPExiftool\Driver\Value\Mono;

class MediaMeta
{
    private $meta = null;

    private $width = null;
    private $height = null;
    private $lat = null;
    private $lng = null;
    private $angle = null;

    /**
     * @param array $source
     */
    public function __construct(array $source)
    {
        $this->meta = $source;
        $this->parseFromMeta($source);
    }

    /**
     * Parse meta data to get expected information
     * @param array $meta
     */
    protected function parseFromMeta($meta)
    {
        $size = self::extractSize($meta);
        if ($size) {
            $this->width = $size[0];
            $this->height = $size[1];
        }

        $geo = self::extractGeotags($meta);
        if ($geo) {
            $this->lat = $geo[0];
            $this->lng = $geo[1];
        }

        $this->angle = isset($meta['Composite:Rotation']) ? (int)($meta['Composite:Rotation']->getValue()->asString()) : 0;

    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return int
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @return string in json format
     */
    public function getMetaInJson()
    {
        return ($this->meta) ? json_encode($this->meta) : '{}';
    }

    /**
     * @return int|null
     */
    public function  getTranspose()
    {
        $transpose = null;
        if ($this->angle === 90 || $this->angle === -270) {
            $transpose = 1;
        } else if ($this->angle === 270 || $this->angle === -90) {
            $transpose = 2;
        }
        return $transpose;
    }

    /**
     * @param $meta
     * @return array|bool
     */
    private static function extractSize($meta)
    {
        $width = $height = false;
        if (isset($meta['QuickTime:ImageWidth'], $meta['QuickTime:ImageHeight'])) {
            $width = $meta['QuickTime:ImageWidth'];
            $height = $meta['QuickTime:ImageHeight'];
        } else if (isset($meta['QuickTime:SourceImageWidth'], $meta['QuickTime:SourceImageHeight'])) {
            $width = $meta['QuickTime:SourceImageWidth'];
            $height = $meta['QuickTime:SourceImageHeight'];
        } else if (isset($meta['Composite:ImageSize'])) {
            list ($width, $height) = explode('x', $meta['Composite:ImageSize']->getValue());
        }

        if (!$width || !$height) {
            return false;
        }
        return array((int)$width, (int)$height);
    }

    /**
     * @param $meta
     * @return array|bool
     */
    private static function extractGeotags($meta)
    {
        // geo coordinates may be located in various exif fields:
        $lat = $lng = false;
        if (isset($meta['Composite:GPSLatitude'], $meta['Composite:GPSLongitude'])) {
            $lat = $meta['Composite:GPSLatitude'];
            $lng = $meta['Composite:GPSLongitude'];
        } else if (isset($meta['Composite:GPSPosition'])) {
            list($lat, $lng) = explode(', ', $meta['Composite:GPSPosition']);
        } else if (isset($meta['QuickTime:GPSCoordinates'])) {
            list($lat, $lng) = explode(', ', $meta['QuickTime:GPSCoordinates']);
        }

        $lat = self::convertCoordinate($lat);
        $lng = self::convertCoordinate($lng);

        if (!$lat || !$lng) {
            return false;
        }
        return array($lat, $lng);
    }

    /**
     * @param $coordinate
     * @return float
     */
    private static function convertCoordinate($coordinate)
    {
        if ($coordinate instanceof Metadata) {
            $value = $coordinate->getValue();
            if ($value instanceof Mono) {
                return floatval($value->asString());
            }
            return 0.0;
        }

        // $lat and $lng are defined as:
        // 26 deg 39' 16.56" N
        // 80 deg 10' 35.40" W
        //
        // To convert to float, need to do:
        // D = Hour + Minute/60 + Second/3600
        //
        // If latitude is N the result is positive, if S, negative.
        // If longitude is E the result is positive, if W, negative.

        if (!preg_match('~^([\d]+)[^\d]+([\d]+)\'[^\d]+([\d\.]+)\".*([NSWE])$~', trim($coordinate), $matches)) {
            return false;
        }

        $float = (float)$matches[1] + (float)$matches[2] / 60 + (float)$matches[3] / 3600;

        if (in_array($matches[4], array('S', 'W'))) {
            $float *= -1.0;
        }

        return $float;
    }
}