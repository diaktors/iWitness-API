<?php

namespace Api\V1\Service\Payment;

use ZF\ApiProblem\ApiProblem;

class GoogleInAppService extends InAppPurchaseAbstract
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Google';
    }

    /**
     * @param $data
     * @return bool|ApiProblem
     */
    public function verifyInAppPurchase(array $data)
    {
        $key =
            "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(
                $this->config[$data['appName']]['publicKey'],
                64,
                "\n"
            )
            . '-----END PUBLIC KEY-----';

        //using PHP to create an RSA key
        $key = openssl_get_publickey($key);

        //$signature should be in binary format, but it comes as BASE64.
        $signature = base64_decode($data['signature']);

        //using PHP's native support to verify the signature
        $result = openssl_verify(
            $data['signedData'],
            $signature,
            $key,
            OPENSSL_ALGO_SHA1);

        $result = !(0 === $result && 1 !== $result);
        if (!$result) {
            return new ApiProblem(422, 'Failed Validation', null, null, array(
                'validation_messages' => ['message' => 'Invalid receipt'],
            ));
        }

        return $result;
    }
}