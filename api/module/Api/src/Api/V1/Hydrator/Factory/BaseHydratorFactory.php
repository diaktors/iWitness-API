<?php
namespace Api\V1\Hydrator\Factory;

use Doctrine\ORM\EntityManager;
use Api\V1\Resource\ResourceAbstract;
use Zend\Form\Annotation\Hydrator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZF\Rest\AbstractResourceListener;

/**
 * Class BaseHydratorFactory
 * @package Api\V1\Hydrator\Factory
 */
abstract class BaseHydratorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {

        $locator = $serviceLocator;
        if ($serviceLocator instanceof HydratorPluginManager) {
            $locator = $serviceLocator->getServiceLocator();
        }
        /** @var ResourceAbstract $resource */
        $resource = $this->getResource($locator);
        $entityManager = $locator->get('Doctrine\ORM\EntityManager');
        return $this->getHydrator($entityManager, $resource);
    }


    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param \Api\V1\Hydrator\Factory\BaseResource|\ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    abstract protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource);


    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @internal param \Zend\Stdlib\Hydrator\HydratorPluginManager $hydratorPluginManager
     * @return null | ResourceAbstract
     */
    protected function getResource(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $resource = null;
        //$serviceLocator = $hydratorPluginManager->getServiceLocator();
        $config = $serviceLocator->get('Config');

        foreach ($config['zf-rest'] as $controller) {
            $listenerName = $controller['listener'];
            if ($serviceLocator->has($listenerName)) {
                $listener = $serviceLocator->get($listenerName);
                if (($listener instanceof AbstractResourceListener) && ($listener->getEvent() != null)) {
                    $resource = $listener;
                    break;
                }
            }
        }

        return $resource;
    }
}