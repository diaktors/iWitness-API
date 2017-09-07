<?php

namespace Perpii\Message {

    use Api\V1\Entity\PersonAbstract;
    use Api\V1\Entity\User;
    use Exception;
    use Perpii\Premailer\Premailer;
    use Psr\Log\LoggerInterface;
    use SlmMail\Mail\Transport\HttpTransport;
    use Zend\Mail\Message;
    use Zend\Mail\Transport\Sendmail;
    use Zend\Mail;
    use Zend\Mime\Message as MimeMessage;
    use Zend\Mime\Part as MimePart;
    use Zend\View\Model\ViewModel;
    use Zend\View\Model;
    use Zend\View\Renderer;
    use Zend\View\Renderer\PhpRenderer;
    use Zend\View\Resolver;

    class EmailManager extends MessageManagerAbstract
    {
        /**
         * @var \Zend\View\Renderer\PhpRenderer
         */
        protected $viewRenderer;
        /**
         * @var string
         */
        private $senderEmail;

        /**
         * @var string
         */
        private $replyToEmail;
        /**
         * @var string
         */
        private $replyToName;
        /**
         * @var string
         */
        private $senderName;
        /**
         * @var string
         */
        private $receiver;
        /**
         * @var string
         */
        private $template;
        /**
         * @var array|object
         */
        private $templateData;

        /**
         * @var Premailer
         */
        private $premailer;

        /** @var string */
        private $subject;

        /** @var HttpTransport */
        private $transport;

        /** @var array */
        private $webConfig;

        public function __construct(
            PhpRenderer $viewRenderer,
            array $config,
            LoggerInterface $logger,
            Premailer $premailer,
            $transport)
        {
            parent::__construct($config, $logger);

            $this->viewRenderer = $viewRenderer;
            $this->premailer    = $premailer;
            $this->transport    = $transport;
            $this->webConfig    = $config['web'];
        }

        /**
         * Set sender name
         * @param $senderName
         * @internal param $sender
         * @return EmailManager
         */
        public function setSenderName($senderName)
        {
            $this->senderName = $senderName;

            return $this;
        }

        /**
         * Set sender email
         * @param $email
         * @return $this
         */
        public function setSenderEmail($email)
        {
            $this->senderEmail = $email;

            return $this;
        }

        /**
         * Set receiver email
         *
         * @param $receiver
         * @return EmailManager
         */
        public function setReceiver($receiver)
        {
            $this->receiver = $receiver;

            return $this;
        }

        /**
         * Set reply to info
         *
         * @param $email
         * @param $name
         * @return $this
         */
        public function setReplyTo($email, $name)
        {
            $this->replyToEmail = $email;
            $this->replyToName = $name;

            return $this;
        }

        /**
         * Set Sms template content
         *
         * @param $template
         * @return EmailManager
         */
        public function setTemplate($template)
        {
            $this->template = $template;

            return $this;
        }

        /**
         * Set Sms template content
         *
         * @param $data
         * @return EmailManager
         */
        public function setTemplateData($data)
        {
            $this->templateData = $data + array(
                    'config'        => $this->config,
                    'web'           => $this->webConfig,
                    'secureBaseUrl' => $this->webConfig['secureBaseUrl'],
                    'baseUrl'       => $this->webConfig['baseUrl'],
                    'apiBaseUrl'    => $this->config['api']['baseUrl'],
                );

            return $this;
        }

        /**
         * @param $template
         * @param array $templateData
         * @return mixed|string
         */
        private function renderHtml($template, array $templateData)
        {
            $model = new ViewModel();
            $model
                ->setVariables($templateData)
                ->setTemplate($template);

            return $this->viewRenderer->render($model);
        }

        /**
         * @param $modelContent
         * @internal param $template
         * @internal param array $templateData
         * @return string
         */
        private function renderEmail($modelContent)
        {
            //Render layout
            $layoutContent = $this->renderHtml(
                'site/layout',
                $this->templateData + array('content' => $modelContent) + array('fallbackSender'=>array($this->senderEmail, $this->senderName))
            );

            return $this
                ->premailer
                ->setCssPath($this->webConfig['premailerCssPath'])
                ->setMarkup($layoutContent)
                ->getConvertedHtml();
        }

        /**
         * Send message to the target
         *
         * @return bool
         */
        public function send()
        {
            //render content
            $contentModel = new ViewModel();
            $contentModel
                ->setVariables($this->templateData)
                ->setTemplate($this->template);
            $contentResult = $this->viewRenderer->render($contentModel);

            $this->subject = $contentModel->subject ? : 'Notification Email';

            //render email with layout
            $content = $this->renderEmail($contentResult, $this->templateData);

            $body       = new MimeMessage();
            $html       = new MimePart($content);
            $html->type = "text/html";
            $body->setParts(array($html));

            // instance mail
            $mail = new Message();
            $mail
                ->setTo($this->receiver)
                ->setBody($body)
                ->setSubject($this->subject);

            $mail->setEncoding('UTF-8');

//            if ($this->senderEmail) {
//                $mail->setFrom($this->senderEmail, $this->senderName);
//            } else {
                //Aws requires at lease 1 from address.
                $mail->setFrom($this->webConfig['fromAddress'], $this->webConfig['fromName']);
//            }

            if ($this->replyToEmail && $this->replyToName) {
                $mail->setReplyTo($this->replyToEmail, $this->replyToName);
            }

            $this->transport->send($mail);

        }
    }
}
