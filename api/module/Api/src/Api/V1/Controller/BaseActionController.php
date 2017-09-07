<?php

namespace Api\V1\Controller;

use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Security\Authorization\AuthorizationTrait;
use Perpii\Log\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ApiProblem\ApiProblem;

abstract class  BaseActionController extends AbstractActionController implements LoggerAwareInterface
{
    use LoggerTrait;
    use AuthorizationTrait;


    public function __construct(
        AuthenticationServiceInterface $authentication,
        AuthorizationInterface $authorization,
        LoggerInterface $logger)
    {
        $this->setAuthorization($authorization);
        $this->setAuthentication($authentication);
        $this->setLogger($logger);
    }

    /**
     * Rendering media type with media content type and media content available
     * @param $path
     * @param $contentType
     * @param array $options
     * @throws \Exception
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Stdlib\ResponseInterface
     */
    public function sendFile($path, $contentType, $options = array())
    {
        $this->debug('Begin to send file ' . $path);
        $this->debug('Content type is ' . $contentType);
        $filePath = realpath($path);

        $response = $this->getResponse();
        $responseHeader = $response->getHeaders();
        $responseHeader
            ->addHeaderLine('Content-Type', $contentType)
            ->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-Control', 'max-age=0')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . basename($path) . '"');

        $usexFileForByteRange = (bool)$options['useXSendFileForByteRangeRequest'];
        $usexFileForNonByteRange = (bool)$options['useXSendFileForNonByteRangeRequest'];
        $isByteRageRequest = isset($_SERVER['HTTP_RANGE']);
        //is use xSendFile case
        if (($isByteRageRequest && $usexFileForByteRange) || (!$isByteRageRequest && $usexFileForNonByteRange)) {
            //case 1: use X-SendFile apache plugin
            $this->debug('Use X-Sendfile to send file  ' . $filePath);
            $responseHeader->addHeaderLine('X-Sendfile', $filePath);
        } else {
            //manual load file
            if (($filePath === false) || empty($filePath) || (!file_exists($filePath))) {
                $this->debug('File ' . $filePath . ' does not exist');
                throw new \Exception('File ' . $filePath . ' does not exist');
            }
            $fd = fopen($filePath, 'rb');

            //manual load file
            if ($fd === false) {
                $this->debug('Could not open file ' . $filePath);
                throw new \Exception('Could not open file ' . $filePath);
            }

			$contentLength = filesize($filePath);

            //case 2: no byte-range
            if (!$isByteRageRequest) {
                $this->debug('no range, send the whole file');
                $responseHeader
                    ->addHeaderLine('Accept-Ranges', 'bytes')
                    ->addHeaderLine('Content-Length', $contentLength)
                    ->addHeaderLine('ETag: "' . md5_file($filePath) . '"');
                $response->setContent(fread($fd, $contentLength));
            } else {
                //case3: load range byte range
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                $range = explode('-', $range);
                $begin = $range[0];
                $end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $contentLength - 1;


                //See if value range requested and return Partial Content headers
                if (($begin > 0 || $end < $contentLength)) {
                    $responseHeader
                        ->addHeaderLine('Accept-Ranges', 'bytes')
                        ->addHeaderLine("Content-Range: bytes $begin-$end/$contentLength")
                        ->addHeaderLine('Content-Length', ($end - $begin) + 1);

                    //$response->setStatusCode('HTTP/1.1 206 Partial Content');
                    $response->setVersion(Request::VERSION_11)
                        ->setStatusCode('206')
                        ->setReasonPhrase('Partial Content');

                }
                //Return Remaining Headers
                $responseHeader->addHeaderLine('ETag: "' . md5_file($path) . '"');
                fseek($fd, $begin);
                $response->setContent(fread($fd, ($end - $begin) + 1));
            }

            fclose($fd);
        }
        return $response;
    }

    /**
     * is administrator
     * @return bool
     */
    protected function  isAdmin()
    {
        $identity = $this->getIdentity();
        if ($identity && $identity->isAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * @param \Exception $ex
     * @return ApiProblemModel
     */
    protected function processUnhandledException(\Exception $ex)
    {
        $this->error($ex->getMessage());
        $code = $ex->getCode();
        $code = $code > 0 ? $code : 500;
        return new ApiProblemModel(new ApiProblem($code, $ex->getMessage()));
    }
}
