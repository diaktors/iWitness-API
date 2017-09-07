<?php
namespace Api\V1\Service\Payment;

use Zend\Http\Client;
use ZF\ApiProblem\ApiProblem;

class AppleInAppService extends InAppPurchaseAbstract
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Apple';
    }

    /**
     * @param array $data
     * @return boolean|ApiProblem
     */
    public function verifyInAppPurchase(array $data)
    {   
        $postData = json_encode(
            array('receipt-data' => $data['receiptData'], 'password' => $this->config['sharedSecret'])
        );

		if(isset($data['sandboxtest']))
			$ch = curl_init($this->config['verifySandboxReceiptUrl']);// for testing
		else
        	$ch = curl_init($this->config['verifyReceiptUrl']);//for production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);
		
        if ($errNo != 0) {			
           return new ApiProblem(422, 'Failed Validation', null, null, array(
               'validation_messages' => $errMsg,
            ));
        }

        // parse the response data
        $data = json_decode($response);

        if (!is_object($data)) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => ['message' => 'Invalid response from app store'],
            ));
        }

        if (!isset($data->status) || $data->status != 0) {
			if($data->status==21007){
		$ch = curl_init($this->config['verifySandboxReceiptUrl']);// for testing
		
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);
		
        if ($errNo != 0) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => $errMsg,
            ));
        }

        // parse the response data
        $data = json_decode($response);

        if (!is_object($data)) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => ['message' => 'Invalid response from app store'],
            ));
        }

        if (!isset($data->status) || $data->status != 0) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => ['message' => 'Invalid receipt'],
            ));
        }

        return true;
			}else{
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => ['message' => 'Invalid receipt'],
            ));
			}
        }

        return true;
    }
	 
}
