<?php

namespace Perpii\Message {

    use Exception;
    use NexmoMessage;
    use Psr\Log\LoggerInterface;
    use Zend\View\Model\ViewModel;
	use Zend\View\Renderer\PhpRenderer;

    class SmsManager extends MessageManagerAbstract
    {
        /**
         * @var \Zend\View\Renderer\PhpRenderer
         */
        protected $viewRenderer;

        /**
         * @var string
         */
        private $sender;

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

        public function __construct(
            PhpRenderer $viewRenderer,
            array $config,
            LoggerInterface $logger)
        {
            parent::__construct($config, $logger);
            $this->viewRenderer = $viewRenderer;
        }

        /**
         * Set sender email
         *
         * @param $sender
         * @return SmsManager
         */
        public function setSender($sender)
        {
            $this->sender = $sender;

            return $this;
        }

        /**
         * Set receiver email
         *
         * @param $receiver
         * @return SmsManager
         */
        public function setReceiver($receiver)
        {
            $this->receiver = $receiver;

            return $this;
        }

        /**
         * Set Sms template content
         *
         * @param $template
         * @return SmsManager
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
         * @return SmsManager
         */
        public function setTemplateData($data)
        {
            $this->templateData = $data;

            return $this;
        }

        /**
         * Send message to the target
         *
         * @throws \Exception
         */
        public function send()
        {

            //$model = new ViewModel();

            if ($this->templateData) {
                $this->templateData = is_array($this->templateData)
                    ? $this->templateData
                    : array($this->templateData);
            } else {
                $this->templateData = array();
			}
			if ($this->templateData['template'] == 0 || $this->templateData['template'] ==1){
			    $alertlink = $this->config['web']['secureBaseUrl'] . $this->templateData['alertLink'];
			    $URL = "http://tinyurl.com/api-create.php?url={$alertlink}";
				$ch = curl_init($URL);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
				$link = curl_exec($ch);
				curl_close($ch);
            	$data = array_merge(
                	array(
                   		// 'config'        => $this->config,
                   		// 'from'          => $this->sender,
                    	//'web'           => $this->config['web'],
                    	//'secureBaseUrl' => $this->config['web']['secureBaseUrl'],
						//'baseUrl'       => $this->config['web']['baseUrl'],
						'link'          => $link,
                	),
                	$this->templateData
				);
			}
			else{
				$data = $this->templateData;
			}


            //$model->setVariables($data);
            //$model->setTemplate($this->template);
            //$content = $this->viewRenderer->render($model);
            $nexmoConf = $this->config['nexmo'];

			//$from = $this->sender ? $this->sender : $nexmoConf['from'];
			//$from = $this->sender;
			$from = $nexmoConf['from'];

            /** @var NexmoMessage $nexmo */
            $nexmo = new NexmoMessage($nexmoConf['key'], $nexmoConf['secret']);
			$nexmoResult  = $nexmo->sendText($this->receiver, $from, $data);
			//echo "<pre>";print_r($nexmoResult);exit;

            if (!$nexmoResult || empty($nexmoResult->messagecount)) {
                $this->logger->error('Could not send SMS to ' . $this->receiver, ['Nexmo Result' => $nexmoResult]);
            }
        }
    }
}
