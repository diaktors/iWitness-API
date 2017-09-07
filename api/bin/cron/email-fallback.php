#!/usr/bin/php
<?php
require dirname(__FILE__) . '/../Bootstrap.php';

use Api\V1\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Perpii\Util\String;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Webonyx\Util\UUID;
use Zend\Json;

use Zend\ServiceManager\ServiceManager;

class EmailFallback implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    const FALLBACK_TABLE = '`email_fallback`';
    const FAILURE_TITLE = 'Delivery Status Notification (Failure)';
    const FAILURE_PREFIX = 'Undeliverable:';

    /**
     * @var  ServiceManager;
     */
    private $serviceManager = null;

    /** @var array */
    private $webConfig = null;

    private $awsConfig = null;

    /** @var SettingService */
    private $settingService = null;

    /** @var EntityManager */
    private $entityManager = null;

    /** @var Swift_Transport */
    private $transport = null;

    public function __construct()
    {
        Bootstrap::initializeLogger('logger-email');
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->setLogger($this->serviceManager->get('Psr\Log\LoggerInterface'));

        //configuration
        $config = $this->serviceManager->get('config');
        $this->webConfig = $config['web'];
        $this->awsConfig = $config['aws'];

        $this->entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
        $this->settingService = $this->serviceManager->get('Api\\V1\\Service\\SettingService');

        //get transport
        if ($config['aws']['useAwsTransport'] === true) {
            $endpoint = 'https://email.' . $config['aws']['region'] . '.amazonaws.com/';
            $this->transport = new Swift_AWSTransport($config['aws']['key'], $config['aws']['secret'], false, $endpoint);
        } else {
            $this->transport = Swift_MailTransport::newInstance();
        }

    }

    /**
     * Execute Automated Recurring Billing (ARB) updating
     */
    public function  run()
    {
        $this->debug("Begin to process failure emails");

        //clean up yesterday data
        $this->deleteYesterdayLog();

        /** @var resource $imapStream */
        $imapStream = null;


        try {
            if (!isset($this->webConfig['fallback'])) {
                throw new \Exception('API server is not configured with an "fallback" email address', 500);
            }

            $server = $this->webConfig['fallback']['server'];
            if (isset($server['useSsl']) && $server['useSsl']) {
                $server['ssl'] = 'ssl';
            } else {
                $server['ssl'] = '';
            }

            if (!isset($server['folder'])) {
                $server['folder'] = 'INBOX';
            }

            $mailbox = sprintf('{%s:%s/imap/%s}%s', $server['host'], $server['port'], $server['ssl'], $server['folder']);

            $this->debug("Mailbox = " . $mailbox);

            /** @var  $mailbox */
            $imapStream = imap_open($mailbox, $this->webConfig['fromAddress'], $server['password'], OP_READONLY);

            if (!$imapStream) {
                $this->debug("Could not connect to server " . $mailbox);
                $this->debug(imap_last_error());
                return;
            }

            /*
                As PHP uses IMAP2 search facilities which don't allow searching by time, this probably is the only solution.
                Or you could look at RECENT flag, but that still requires looping through all messages of the day.
            */
            $date = date("d M Y", time());
            $uids = imap_search($imapStream, 'UNDELETED UNANSWERED SUBJECT "Undeliverable" ON "' . $date . '"', SE_UID);
            if ($uids) {
                $this->debug('Found ' . count($uids) . ' emails');
                foreach ($uids as $uid) {
                    //has sent to client
                    if ($this->hasForward($uid)) {
                        $this->debug("Email id $uid has been processed, ignore it");
                        continue;
                    }
                    $this->processEmailUid($imapStream, $uid);
                    //log sent email
                    $this->addEmailIdToLog($uid);
                }
            } else {
                $this->debug('No failure email found');
            }
        } catch (\Exception $ex) {
            $this->debug($ex->getMessage());
        } finally {
            //close connection
            if ($imapStream) {
                imap_close($imapStream);
            }
        }

        $this->debug('End of process failure emails ');
    }

    /**
     * @param $imapStream
     * @param $uid
     */
    private function processEmailUid($imapStream, $uid)
    {
        try {
            $this->debug("Begin to process email id $uid");
            $overview = imap_fetch_overview($imapStream, $uid, FT_UID);
            if (!$overview || !isset($overview[0]->subject)) {
                $this->error("\nCannot load email $uid header");
                return;
            }

            $subject = trim($overview[0]->subject);
            if (!$this->isFailureEmail($subject)) {
                $this->debug("Ignore email \"$subject\"");
                return;
            } else {
                $this->debug("Processing email subject  \"$subject\"");
            }

            $structure = imap_fetchstructure($imapStream, $uid, FT_UID);
            if (!isset($structure->parts) || count($structure->parts) <= 0) {
                $this->error("No part found in email message, ignore");
                return;
            }

            $message = Swift_Message::newInstance();
            $section = 0;
            foreach ($structure->parts as $part) {
                $section++;
                //main section
                if ($section === 1) {
                    if ($part->type == 1) { //multipart
                        if (isset($part->parts) && count($part->parts) > 0) {
                            $j = 0;
                            foreach ($part->parts as $subPart) {
                                $j++;
                                $content = $this->getPartContent($imapStream, $uid, '' . $section . '.' . $j, $subPart);
                                $type = $this->getType($subPart);

                                if ($type == 'text/html') {
                                    $message->setBody($content, $type);
                                } else {
                                    $message->addPart($content, $type);
                                }
                            }
                        }
                    } else { //other read as text
                        $content = $this->getPartContent($imapStream, $uid, $section, $part);
                        $type = $this->getType($part);
                        $message->setBody($content, $type);
                    }
                    //others section
                } else {
                    if (strtoupper($part->subtype) == 'RFC822') {
                        $rfc822HeaderText = imap_fetchbody($imapStream, $uid, $section . ".0", FT_UID);
                        $rfc822Header = imap_rfc822_parse_headers($rfc822HeaderText);

                        $subPart = $part->parts[0];
                        $type = $this->getType($subPart);
                        $content = $this->getPartContent($imapStream, $uid, $section . ".1", $subPart);
                        $rfc822Content = $content;
                        $fileName = isset($rfc822Header->Subject) ? $rfc822Header->Subject : '';
                        $fileName = str_replace(' ', '_', $fileName) . strtolower($subPart->subtype);
                    } else {

                        if ($part->subtype == 'DELIVERY-STATUS') {
                            $fileName = 'Document.txt';
                        } else {
                            $fileName = "Document_$section.txt";
                        }
                        $type = $this->getType($part);
                        $content = $this->getPartContent($imapStream, $uid, $section, $part);
                    }
                    $message->attach(Swift_Attachment::newInstance($content, $fileName, $type));
                }
            }

            if (!isset($rfc822Header) || !isset($rfc822Header->from[0])) {
                $this->error('Could not found RFC822 email content');
                return;
            }

            $sender = $this->extractSender($rfc822Content);
            if (empty($sender['email'])) {
                $this->error('Could not extract sender from email content, please check it at layout.phtml file');
                return;
            }

            //check loop
            $fromAddress = $rfc822Header->from[0]->mailbox . '@' . $rfc822Header->from[0]->host;
            if (strcmp($sender['email'], $fromAddress) == 0) {
                $this->error('Users send email to themselves, ignore it');
                return;
            }

            $message
                ->setSubject($subject)
                ->setFrom($this->webConfig['fromAddress'], $this->webConfig['fromName'])
                ->setTo($sender['email']);

            //Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($this->transport);
            $sent = $mailer->send($message);
            $this->error("$sent emails were sent \n");
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

    /**
     * @param $content
     * @return array
     */
    private function extractSender($content)
    {
        $sender = array('name' => '', 'email' => '');
        preg_match('/\<sender-email\>(.*?)\<\/sender-email\>/', $content, $match);
        if (isset($match[1])) {
            $sender['email'] = $match[1];
        }
        preg_match('/\<sender-name\>(.*?)\<\/sender-name\>/', $content, $match);
        if (isset($match[1])) {
            $sender['name'] = $match[1];
        }
        return $sender;
    }

    /**
     * @param $part
     * @return string
     */
    public function getType($part)
    {
        return isset($part->subtype) && $part->subtype == 'HTML' ? 'text/html' : 'text/plain';
    }

    /**
     * @param $imapStream
     * @param $uid
     * @param $section
     * @param $part
     * @return string
     */
    private function getPartContent($imapStream, $uid, $section, $part)
    {
        $params = $this->extractParams($part);

        $data = imap_fetchbody($imapStream, $uid, $section, FT_UID);

        switch ($part->encoding) {
            case 1:
                $data = imap_utf8($data);
                break;
            case 2:
                $data = imap_binary($data);
                break;
            case 3:
                $data = imap_base64($data);
                break;
            case 4:
                $data = imap_qprint($data);
                break;
        }

        //convert to UTF-8 as default
        if (!empty($params['charset'])) {
            $data = iconv(strtoupper($params['charset']), 'UTF-8//IGNORE', $data);
        }
        return $data;
    }

    /**
     * @param $part
     * @return array
     */
    private function extractParams($part)
    {
        $params = array();
        if (!empty($part->parameters)) {
            foreach ($part->parameters as $param) {
                $paramName = strtolower(preg_match('~^(.*?)\*~', $param->attribute, $matches) ? $matches[1] : $param->attribute);
                if (isset($params[$paramName])) {
                    $params[$paramName] .= $param->value;
                } else {
                    $params[$paramName] = $param->value;
                }
            }
        }
        return $params;
    }

    /**
     * @param $emailSubject
     * @return bool
     */
    private function isFailureEmail($emailSubject)
    {
        return strcmp(self::FAILURE_TITLE, $emailSubject) == 0 || String::startsWith($emailSubject, self::FAILURE_PREFIX);
    }

    /**
     * @param $uid
     * @return bool
     */
    private function hasForward($uid)
    {
        //get from cache
        static $data;
        if (!$data) {
            $data = $this->loadSendingLog();
        }
        return in_array($uid, $data);
    }

    private function deleteYesterdayLog()
    {
        //Use raw SQL will have better performance
        try {
            $date = new \DateTime();
            $date->setTime(0, 0, 0);
            $conn = $this->entityManager->getConnection();
            $sql = 'DELETE FROM ' . self::FALLBACK_TABLE . ' WHERE `created` < ?';
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $date->getTimestamp());
            $stmt->execute();
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * @return array
     */
    private function loadSendingLog()
    {
        //Use raw SQL will have better performance
        //load and cache data
        $data = array();
        $conn = $this->entityManager->getConnection();
        $emailFallbacks = $conn->fetchAll('SELECT `email_id` FROM ' . self::FALLBACK_TABLE);

        if ($emailFallbacks) {
            foreach ($emailFallbacks as $fallback) {
                $data[] = $fallback['email_id'];
            }
        }
        return $data;
    }

    private function addEmailIdToLog($uid)
    {
        $fallBack = new \Api\V1\Entity\EmailFallback(UUID::generate(), $uid);
        $this->entityManager->persist($fallBack);
        $this->entityManager->flush($fallBack);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}


$emailFallback = new EmailFallback();
$emailFallback->run();