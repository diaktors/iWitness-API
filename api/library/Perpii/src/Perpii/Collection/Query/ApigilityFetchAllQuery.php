<?php

namespace Perpii\Collection\Query;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\ServiceManager\AbstractPluginManager;

interface ApigilityFetchAllQuery extends ObjectManagerAwareInterface
{
    /**
     * Set the select callback function
     *
     * @param Callable $selectCallback
     */
    function setSelectCallback(callable $selectCallback);

    /**
     * @param string $entityClass
     * @param array $parameters
     *
     * @return mixed This will return an ORM or ODM Query\Builder
     */
    public function createQuery($entityClass, array $parameters);

    /**
     * @param   $queryBuilder
     *
     * @return AdapterInterface
     */
    public function getPaginatedQuery($queryBuilder);

    /**
     * @param   $entityClass
     *
     * @return int
     */
    public function getCollectionTotal($entityClass);

}
