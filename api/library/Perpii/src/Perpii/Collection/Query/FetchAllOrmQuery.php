<?php

namespace Perpii\Collection\Query;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Perpii\Collection\Filter\ORM\Equals;
use Perpii\Paginator\Adapter\DoctrineOrmAdapter;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class FetchAllOrmQuery
 *
 * @package Perpii\Resource\Query
 */
class FetchAllOrmQuery implements ApigilityFetchAllQuery
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var callable
     */
    private $selectCallback;

    private static $typeMapping = array(
        'eq' => 'Perpii\Collection\Filter\ORM\Equals',
        'neq' => 'Perpii\Collection\Filter\ORM\NotEquals',
        'lt' => 'Perpii\Collection\Filter\ORM\LessThan',
        'lte' => 'Perpii\Collection\Filter\ORM\LessThanOrEquals',
        'gt' => 'Perpii\Collection\Filter\ORM\GreaterThan',
        'gte' => 'Perpii\Collection\Filter\ORM\GreaterThanOrEquals',
        'isnull' => 'Perpii\Collection\Filter\ORM\IsNull',
        'isnotnull' => 'Perpii\Collection\Filter\ORM\IsNotNull',
        'in' => 'Perpii\Collection\Filter\ORM\In',
        'notin' => 'Perpii\Collection\Filter\ORM\NotIn',
        'between' => 'Perpii\Collection\Filter\ORM\Between',
        'like' => 'Perpii\Collection\Filter\ORM\Like',
        'notlike' => 'Perpii\Collection\Filter\ORM\Like',
    );


    protected $filterManager;

    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Set the select callback function
     *
     * @param Callable $selectCallback
     */
    public function setSelectCallback(callable $selectCallback)
    {
        $this->selectCallback = $selectCallback;
    }

    /**
     * @param string $entityClass
     * @param array $parameters
     *
     * @return mixed This will return an ORM or ODM Query\Builder
     */
    public function createQuery($entityClass, array $parameters)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();

        $select = array('row');
        if ($this->selectCallback && is_callable($this->selectCallback)) {
            $select = array_merge($select, call_user_func($this->selectCallback));
        }

        $queryBuilder->select($select)
            ->from($entityClass, 'row');

        // Get metadata for type casting
        $cmf = $this->getObjectManager()->getMetadataFactory();
        $entityMetaData = $cmf->getMetadataFor($entityClass);
        $metadata = (array)$entityMetaData;

        // Orderby
        if (!isset($parameters['orderBy'])) {
            $parameters['orderBy'] = array($entityMetaData->getIdentifier()[0] => 'asc');
        }

        foreach ($parameters['orderBy'] as $fieldName => $sort) {
            if ($entityMetaData->hasField($fieldName)) {
                $queryBuilder->addOrderBy("row.$fieldName", $sort);
            } else {
                error_log('Couldn\'t found sort field ' . $fieldName . ', value ' . $sort);
            }
        }

        if (isset($parameters['query'])) {
            foreach ($parameters['query'] as $field => $value) {
                if ($entityMetaData->hasField($field)) {
                    $filter = new Equals();
                    $option = array('where' => 'and', 'field' => $field, 'value' => $value);
                    $filter->filter($queryBuilder, $metadata, $option);
                } else {
                    error_log('Couldn\'t found query field ' . $field . ', value ' . $value);
                }
            }
        }


        return $queryBuilder;
    }


    /**
     * @param   $queryBuilder
     *
     * @return AdapterInterface
     */
    public function getPaginatedQuery($queryBuilder)
    {
        $adapter = new DoctrineOrmAdapter($queryBuilder->getQuery(), false);

        return $adapter;
    }

    /**
     * @param   $entityClass
     *
     * @return int
     */
    public function getCollectionTotal($entityClass)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $cmf = $this->getObjectManager()->getMetadataFactory();
        $entityMetaData = $cmf->getMetadataFor($entityClass);

        $queryBuilder->select('count(row.' . $entityMetaData->getIdentifier()[0] . ')')
            ->from($entityClass, 'row');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }
}
