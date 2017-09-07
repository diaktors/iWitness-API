<?php

namespace Api\V1\Service\Payment;

use PayPal\EBLBaseComponents\ActivationDetailsType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\PersonNameType;
use PayPal\EBLBaseComponents\PayerInfoType;
use PayPal\EBLBaseComponents\CreditCardDetailsType;
use PayPal\EBLBaseComponents\DoDirectPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\Exception\PPConfigurationException;
use PayPal\Exception\PPConnectionException;
use PayPal\Exception\PPInvalidCredentialException;
use PayPal\Exception\PPMissingCredentialException;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\DoDirectPaymentReq;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\DoDirectPaymentRequestType;


//todo: In the future should use Paypal REST API,
//this code still use old API because lack of ARB in current REST version

class PaypalService extends PaymentAbstract
{
    /**
     * @param array $data
     * @return mixed
     */
    function createBilling(array $data)
    {
        try {
            list($firstName, $lastName) = $this->getSubscriberName($data);
            $address = $this->getBillingAddress($data);

            //information about the payer
            $personName = new PersonNameType();
            $personName->FirstName = $firstName;
            $personName->LastName = $lastName;
            $payer = new PayerInfoType();
            $payer->PayerName = $personName;
            $payer->Address = $address;
            $payer->PayerCountry = $data['country'];

            $cardDetails = $this->getCardDetails($data);
            $cardDetails->CardOwner = $payer;

            //payment details
            $paymentDetailsItem = new PaymentDetailsItemType();
            $paymentDetailsItem->Name = $data['plan'];
            $paymentDetailsItem->Amount = $data['amount'];
            $paymentDetailsItem->Quantity = $data['quantity'];
            $paymentDetailsItem->PromoCode = $data['promoCode'];
            $paymentDetailsItem->Description = $data['description'];

            $paymentDetails = new PaymentDetailsType();
            $paymentDetails->ShipToAddress = $address;
            $paymentDetails->OrderTotal = new BasicAmountType('USD', $data['amount']);
            $paymentDetails->InvoiceID = $data['subscriptionId'];
            //$paymentDetails->PaymentDetailsItem = $paymentDetailsItem;

            $ddReqDetails = new DoDirectPaymentRequestDetailsType();
            $ddReqDetails->CreditCard = $cardDetails;
            $ddReqDetails->PaymentDetails = $paymentDetails;
            $ddReqDetails->IPAddress = isset($data['customerIp']) ? $data['customerIp'] : '';

            $doDirectPaymentReq = new DoDirectPaymentReq();
            $doDirectPaymentReq->DoDirectPaymentRequest = new DoDirectPaymentRequestType($ddReqDetails);

            $paypalService = new PayPalAPIInterfaceServiceService($this->config);

            /** @var \PayPal\PayPalAPI\DoDirectPaymentResponseType $doDirectPaymentResponse */
            $doDirectPaymentResponse = $paypalService->DoDirectPayment($doDirectPaymentReq);
            //$this->debug(print_r($doDirectPaymentResponse, true));

            if ($doDirectPaymentResponse->Ack == 'Failure') {
                return new PaymentResult(false, null, $doDirectPaymentResponse->Errors[0]->LongMessage);
            } else {
                return new PaymentResult(true, $doDirectPaymentResponse->TransactionID, '');
            }
        } catch (\Exception $ex) {
            return $this->processException($ex);
        }
    }

    /**
     * Create Automated Recurring Billing
     * @param array $data
     * @return mixed
     */
    function createARBBilling(array $data)
    {
        try {

            $months = $data['months'];
            if ($months <= 12) {
                return new PaymentResult(false, '', 'Month must be less than or equals 12');
            }

            $expire = $data['expire'];

            list($firstName, $lastName) = $this->getSubscriberName($data);

            $address = $this->getBillingAddress($data);


            $RPProfileDetails = new RecurringPaymentsProfileDetailsType();
            $RPProfileDetails->SubscriberName = "$firstName $lastName";
            $RPProfileDetails->BillingStartDate = $expire->format(DATE_ATOM);
            $RPProfileDetails->SubscriberShippingAddress = $address;

            $activationDetails = new ActivationDetailsType();

            //optional
            $activationDetails->InitialAmount = new BasicAmountType('USD', 0);
            $activationDetails->FailedInitialAmountAction = 'ContinueOnFailure';

            $paymentBillingPeriod = new BillingPeriodDetailsType();
            $paymentBillingPeriod->BillingPeriod = 'Year';
            $paymentBillingPeriod->BillingFrequency = 'Month';
            $paymentBillingPeriod->TotalBillingCycles = 12;

            $paymentBillingPeriod->Amount = new BasicAmountType('USD', $data['amount']);
            $paymentBillingPeriod->ShippingAmount = new BasicAmountType('USD', 0);
            $paymentBillingPeriod->TaxAmount = new BasicAmountType('USD', 0);


            $scheduleDetails = new ScheduleDetailsType();
            $scheduleDetails->Description = isset($anetConfig['subscription']['name']) ? $anetConfig['subscription']['name'] : '';
            $scheduleDetails->ActivationDetails = $activationDetails;
            $scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
            $scheduleDetails->MaxFailedPayments = 3;
            $scheduleDetails->AutoBillOutstandingAmount = 'NoAutoBill';

            $createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType();
            $cardDetails = $this->getCardDetails($data);


            $createRPProfileRequestDetail->CreditCard = $cardDetails;
            $createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
            $createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;

            $createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
            $createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;

            $createRPProfileReq = new CreateRecurringPaymentsProfileReq();
            $createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;

            $paypalService = new PayPalAPIInterfaceServiceService($this->config);


            /** @var \PayPal\PayPalAPI\CreateRecurringPaymentsProfileResponseType $createRPProfileResponse */
            $createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);

            if ($createRPProfileResponse->Ack == 'Failure') {
                return new PaymentResult(false, null, $createRPProfileResponse->Errors[0]->LongMessage);
            } else {
                return new PaymentResult(true, $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID, '');
            }
        } catch (\Exception $ex) {
            return $this->processException($ex);
        }
    }

    /**
     * @param \Exception $ex
     * @return PaymentResult
     */
    private function processException(\Exception $ex)
    {
        $messageDetail = "";
        $message = $ex->getMessage();
        $this->error($message);

        if ($ex instanceof PPConnectionException) {
            $messageDetail = "Error connecting to " . $ex->getUrl();
        } else if ($ex instanceof PPMissingCredentialException || $ex instanceof PPInvalidCredentialException) {
            $messageDetail = $ex->errorMessage();
        } else if ($ex instanceof PPConfigurationException) {
            $messageDetail = "Invalid configuration. Please check your configuration file";
        }

        return new PaymentResult(false, null, $messageDetail);
    }

    /**
     * @param array $data
     * @return AddressType
     */
    private function getBillingAddress(array $data)
    {
        list($firstName, $lastName) = $this->getSubscriberName($data);

        /*
         * shipping address
        */
        $address = new AddressType();
        $address->Name = "$firstName $lastName";
        $address->Street1 = $data['address1'];
        $address->Street2 = $data['address1'];
        $address->CityName = $data['city'];
        $address->StateOrProvince = $data['state'];
        $address->PostalCode = $data['zip'];
        $address->Country = $data['country'];
        $address->Phone = isset($data['originalPhone']) ? $data['originalPhone'] : '';

        return $address;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getSubscriberName(array $data)
    {
        $firstName = isset($data['firstName']) ? $data['firstName'] : '';
        $lastName = isset($data['lastName']) ? $data['lastName'] : '';
        return array($firstName, $lastName);
    }

    /**
     * @param array $data
     * @return CreditCardDetailsType
     */
    private function getCardDetails(array $data)
    {
        $cardDetails = new CreditCardDetailsType();
        $cardDetails->CreditCardNumber = trim((string)$data['cardNum']);
        $cardDetails->CreditCardType = self::formatCreditCardType($data['cardType']);
        $cardDetails->CVV2 = $data['cardCode'];
        $cardDetails->ExpMonth = $data['expMonth'];
        $cardDetails->ExpYear = $data['expYear'];
        return $cardDetails;
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public static function formatCreditCardType($type)
    {
        $allowTypes = [
            'visa' => 'Visa',
            'visa_electron' => 'Visa',
            'mastercard' => 'MasterCard',
            'amex' => 'Amex',
            'discover' => 'Discover'
        ];
        $type = strtolower($type);

        if (!isset($allowTypes[$type])) {
            throw new \Exception('Paypal does not support card type ' . $type);
        }
        return $allowTypes[$type];
    }


    /**
     * @return string
     */
    function getName()
    {
        return 'PayPal';
    }
}