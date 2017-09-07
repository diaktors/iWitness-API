<?php
namespace Webonyx\Util;

/**
 * Class BitField
 *
 * @package Webonyx\Util
 */
class BitField
{
    /**
     * The current set of bits set
     *
     * @var int
     */
    public $_fields = null;

    /**
     * Constructs a new bit field with the given set of bits
     *
     * @param int $value The current bits set
     */
    public function __construct($value)
    {
        $this->_fields = (int)$value;
    }

    /**
     * Magic getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch($name)
        {
            case 'bits':
                return $this->_fields;
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Prints out the actual field that is set
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_fields;
    }

    /**
     * @return int|null
     */
    public function toInt()
    {
        return $this->_fields;
    }

    /**
     * Sets the bits specified
     *
     * @param int $bits
     *
     * @return \Webonyx\Util\BitField
     */
    public function setBits($bits)
    {
        $this->_fields = $this->_fields | $bits;
        return $this;
    }

    /**
     * Unsets the bits specified
     *
     * @param int $bits
     * @return \Webonyx\Util\BitField
     */
    public function unsetBits($bits)
    {
        $inverseMask = ~$bits;
        $this->_fields = $this->_fields & $inverseMask;

        return $this;
    }

    /**
     * Returns if a bit is set or not
     *
     * @param int $bits
     * @return bool
     */
    public function issetBits($bits)
    {
        return (($this->_fields & $bits) == $bits);
    }

    /**
     * Toggles the given bits
     *
     * @param int $bits
     * @return \Webonyx\Util\BitField
     */
    public function toggleBits($bits)
    {
        $this->_fields = $this->_fields ^ $bits;
        return $this;
    }
}