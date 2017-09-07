<?php
namespace Perpii\Message {

    use Zend\Mvc\Service\ViewHelperManagerFactory;
    use Zend\Mvc\Service\ViewResolverFactory;
    use Zend\ServiceManager\ServiceLocatorInterface;
    use Zend\View\Renderer\PhpRenderer;
    use Zend\ServiceManager\FactoryInterface;

    class EmailManagerFactory implements FactoryInterface
    {
        /**
         * Instantiates and configures the renderer's helper manager
         *
         * @param $services
         * @return ViewHelperManager
         */
        public function getHelperManager($services)
        {
            $helperFactory = new ViewHelperManagerFactory();
            return $helperFactory->createService($services);

        }

        /**
         * Instantiates and configures the renderer's resolver
         */
        public function getResolver($services)
        {
            $resolverFactory = new ViewResolverFactory();
            return $resolverFactory->createService($services);

        }

        /**
         * Instantiates and configures the renderer
         *
         * @param $services
         * @return ViewPhpRenderer
         */
        public function getRenderer($services)
        {
            $renderer = new PhpRenderer();
            $renderer->setHelperPluginManager($this->getHelperManager($services));
            $renderer->setResolver($this->getResolver($services));
            $services->setService('ViewRenderer', $renderer);
            $services->setAlias('Zend\View\Renderer\PhpRenderer', 'ViewRenderer');
            $services->setAlias('Zend\View\Renderer\RendererInterface', 'ViewRenderer');

            return $renderer;
        }

        /**
         * Create service
         *
         * @param ServiceLocatorInterface $serviceLocator
         * @return mixed
         */
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            /** @var \Zend\ServiceManager\ServiceManager $services */
            if ($serviceLocator->has('ViewRenderer')) {
                $renderer = $serviceLocator->get('ViewRenderer');
            } else {
                $renderer=  $this->getRenderer($serviceLocator);
            }

            $config = $serviceLocator->get('config');
            $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
            $premailer = $serviceLocator->get('Perpii\\Premailer\\Premailer');

            if ($config['aws']['useAwsTransport'] === true) {
                $transport = $serviceLocator->get('SlmMail\Mail\Transport\SesTransport');
            } else {
                $transport = new \Zend\Mail\Transport\Sendmail();
            }

            return new EmailManager($renderer, $config, $logger, $premailer, $transport);
        }

    }
}
