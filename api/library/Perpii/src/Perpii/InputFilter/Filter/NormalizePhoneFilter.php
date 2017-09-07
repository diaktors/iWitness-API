<?php
namespace Perpii\InputFilter\Filter;

use Zend\Filter\FilterInterface;

class NormalizePhoneFilter implements FilterInterface
{

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $phone
     * @throws \Zend\Filter\Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($phone)
    {
        $phone = preg_replace('~[^0-9]~', '', $phone);
        return self::appendUSCanadaCountryCode($phone);
    }

    /**
     * @param $phone
     * @return string
     */
    public static function appendUSCanadaCountryCode($phone)
    {
        if (10 === strlen($phone)) {
            $phone = '1' . $phone; // Append country code for US/Canada
        }
        return $phone;
    }
}