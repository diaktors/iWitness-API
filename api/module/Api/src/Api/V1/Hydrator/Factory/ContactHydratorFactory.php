<?php
namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\ContactHydrator;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;

/**
 * Class ContactHydratorFactory
 * @package Api\V1\Hydrator\Factory
 */
class ContactHydratorFactory extends BaseHydratorFactory
{
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null )
    {
        return new ContactHydrator($entityManager, $resource);
    }
}