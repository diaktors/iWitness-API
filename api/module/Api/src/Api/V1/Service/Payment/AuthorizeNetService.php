<?php

namespace Api\V1\Service\Payment;


use Webonyx\Util\UUID;

class AuthorizeNetService extends PaymentAbstract
{

    /**
     * @param array $data
     * @return mixed
     */
    function createBilling(array $data)
    {
        //custom fields
        $custom = array('phone_model' => $data['originalPhoneModel'], 'plan' => $data['plan'], 'cardtype' => $data['cardType']);

        //authorize .net fields
        $fields = array(
            'card_num' => $data['cardNum'],
            'card_code' => $data['cardCode'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'],
            'first_name' => isset($data['firstName']) ? $data['firstName'] : '',
            'last_name' => isset($data['lastName']) ? $data['lastName'] : '',
            'customer_ip' => isset($data['customerIp']) ? $data['customerIp'] : '',
            'description' => isset($this->config['description']) ? $this->config['description'] : '',
            //'invoice_num' => $data['subscriptionId'],
            'invoice_num' => str_replace('-', '', $data['subscriptionId']),

            'phone' => isset($data['originalPhone']) ? $data['originalPhone'] : '',
            'cust_id' => isset($data['originalPhone']) ? $data['originalPhone'] : '',
            'exp_date' => $data['expDate'],
            'address' => $data['address'],
            'amount' => $data['amount'],
        );

        $this->debug('Begin to call authorize.net Advanced Integration Method');

        //Authorize.Net  Advanced Integration Method
		$aim = new \AuthorizeNetAIM($this->config['loginId'], $this->config['transactionKey']);
		
		$aim->setSandbox((bool)$this->config['sandbox']);
        
        $aim->setFields($fields);
        $aim->setCustomFields($custom);
        //error_log("Display Auth results",  3, "/volumes/log/api/test-log.log"); 
        if ($this->config['log']) {
            $aim->setLogFile($this->config['log']);
		}
        //error_log(print_r($aim,TRUE),  3, "/volumes/log/api/test-log.log");		
		$aimResult = $aim->authorizeAndCapture();
        //die();
        $this->debug('Result of calling  authorize.net Advanced Integration Method');
        return new PaymentResult($aimResult->approved, $aimResult->transaction_id, $aimResult->response_reason_text);
    }

    /**
     * Create Automated Recurring Billing
     * @param array $data
     * @return mixed
     */
    function createARBBilling(array $data)
    {
        $months = $data['months'];
        $expire = $data['expire'];


        //Automated Recurring Billing
        // Since transaction is valid - also create ARB entry
        // (max length for ARB subscription is 12 months, so we can't have them for 2-years plan):
        if ($months <= 12) {
            $arb = new \AuthorizeNetARB($this->config['loginId'], $this->config['transactionKey']);
            $arb->setSandbox((bool)$this->config['sandbox']);

            if ($this->config['log']) {
                $arb->setLogFile($this->config['log']);
            }
			switch($data["plan"]) {
			case "month":
				$intervalUnit = "months";
				$intervalLength = 1;
				break;
			case "year":
				$intervalUnit = "years";
				$intervalLength = 1;
				break;
			case "2-years":
				$intervalUnit = "years";
				$intervalLength = 2;
				break;
			default:
				break;

			}
            $arbSubscription = new \AuthorizeNet_Subscription();
            $arbSubscription->name = isset($this->config['description']) ? $this->config['description'] : '';
            $arbSubscription->amount = $data['amount'];
            $arbSubscription->customerId = isset($data['originalPhone']) ? $data['originalPhone'] : '';
            $arbSubscription->creditCardCardNumber = $data['cardNum'];
            $arbSubscription->creditCardExpirationDate = $data['expDate'];
            $arbSubscription->creditCardCardCode = $data['cardCode'];
            $arbSubscription->intervalLength = $months;
            $arbSubscription->intervalUnit = $intervalUnit;
            $arbSubscription->intervalLength = $intervalLength;
            $arbSubscription->orderInvoiceNumber = substr(str_replace('-', '', $data['subscriptionId']), 0, 20);
            //$arbSubscription->intervalLength = 50;
            //$arbSubscription->intervalUnit = 'days';

            $arbSubscription->orderDescription = ""; // TODO interval + price
            // since we charge first payment using AIM, ARB start date is at end of this period
            $arbSubscription->startDate = $expire->format("Y-m-d");
            $arbSubscription->totalOccurrences = 9999; // endless

            $arbSubscription->billToAddress = $data['address'];
            $arbSubscription->billToCity = $data['city'];
            $arbSubscription->billToCountry = $data['country'];
            $arbSubscription->billToFirstName = isset($data['firstName']) ? $data['firstName'] : '';
            $arbSubscription->billToLastName = isset($data['lastName']) ? $data['lastName'] : '';
            $arbSubscription->billToState = $data['state'];
            $arbSubscription->billToZip = $data['zip'];
            $arbSubscription->customerId = isset($data['customerId']) ? $data['customerId'] : '';

            $this->debug('Begin to call authorize.net Automated Recurring Billing');
            $arbResult = $arb->createSubscription($arbSubscription);
            $this->debug('Result of calling authorize.net Automated Recurring Billing');

            if ('ok' === strtolower((string)$arbResult->xml->messages->resultCode)) {
                $result = new PaymentResult(true, (string)$arbResult->xml->subscriptionId);
            } else {
                $this->error(print_r($arbResult->xml, true));
                $result = new PaymentResult(false, null, $arbResult->xml->children('message')->text);
            }
        } else {
            $result = new PaymentResult(false, '', 'Month must be less than or equals 12');
        }

        return $result;
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'Authorize.Net';
    }
}
