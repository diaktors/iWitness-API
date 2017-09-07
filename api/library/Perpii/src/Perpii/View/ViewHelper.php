<?php
namespace Perpii\View;

use Zend\View\Helper\AbstractHelper;

class ViewHelper extends AbstractHelper
{
    protected $serviceManager;

    public function __construct($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke()
    {
        return $this;
    }

    /**
     * @param $phone
     * @return mixed|string
     */
    public function formatPhone($phone)
    {
        if (strlen($phone) < 10) {
            return $phone;
        }

        // make sure that valid int'l phone number
        $phone = $this->intlPhone($phone);
        $length = strlen($phone);

        $human = substr($phone, $length - 10, 3) . '-'
            . substr($phone, $length - 7, 3) . '-'
            . substr($phone, $length - 4);

        if ('1' === $phone[0] && $length <= 11) {
            return $human; // US
        }

        return '+' . substr($phone, 0, $length - 10) . '-' . $human;
    }

    public function escape($value)
    {
        return $this->getView()->escapeHtml($value);
    }

    /**
     * Transforms phone number from human-friendly format to international format
     *
     * @param string $phone
     * @return mixed|string
     */
    public function intlPhone($phone)
    {
        $norm = preg_replace('~[^0-9]~', '', $phone);

        if (10 === strlen($norm)) {
            return '1' . $norm; // US
        }

        return $norm;
    }
}