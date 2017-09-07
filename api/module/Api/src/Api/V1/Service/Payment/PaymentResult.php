<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 7/2/14
 * Time: 5:19 PM
 */

namespace Api\V1\Service\Payment;


class PaymentResult
{
    public $status;
    public $message;
    public $billingId;

    public function __construct($status = false, $billingId = null, $message = null)
    {
        $this->status = $status;
        $this->billingId = $billingId;
        $this->message = $message;

    }
} 