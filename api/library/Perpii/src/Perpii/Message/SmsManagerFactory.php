<?php
namespace Perpii\Message {

    use Zend\ServiceManager\FactoryInterface;
    use Zend\ServiceManager\ServiceLocatorInterface;

    class SmsManagerFactory implements FactoryInterface
    {
        /**
         * Create service
         *
         * @param ServiceLocatorInterface $serviceLocator
         * @return mixed
         */
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            $viewRenderer = $serviceLocator->get('ViewRenderer');
            $config = $serviceLocator->get('config');
            $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

            return new SmsManager($viewRenderer, $config, $logger);
        }

    }
}