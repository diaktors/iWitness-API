<?php

namespace Api\V1\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Perpii\Collection\Query\ApigilityFetchAllQuery;
use Perpii\Collection\Query\FetchAllOrmQuery;
use Perpii\Doctrine\Filter\SoftDeletable;
use Perpii\Exception\EntityNotFoundException;
use Perpii\Log\LoggerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class ServiceAbstract implements ServiceInterface, LoggerAwareInterface
{
    use LoggerTrait;

    const ENTITY_CLASS = ''; //late binding

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager = null;


    /** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject */
    protected $hydrator = null;

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->hydrator = new DoctrineObject($entityManager);
        $this->setLogger($logger);
    }

    /**
     * Delete a resource
     *
     * @param  mixed $idOrEntity
     * @return bool
     * @throws EntityNotFoundException
     */
    public function delete($idOrEntity)
    {
        $entity = $this->getEntity($idOrEntity);

        if ($entity instanceof SoftDeletable) {
            $entity->setDeleted(time());
        } else {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
        return true;
    }


    /**
     * Fetch a resource
     *
     * If the extractCollections array contains a collection for this resource
     * expand that collection instead of returning a link to the collection
     *
     * @param  mixed $id
     * @return object
     */
    public function fetch($id)
    {
        return $this->find($id);
    }

    /**
     * Fetch all or a subset of resources
     * @deprecated this function is used in list all resource only, please don't use it elsewhere
     * @see Apigility/Doctrine/Server/Resource/AbstractResource.php
     * @param array $queryParams
     * @param callable $select
     * @param callable $where
     * @param string $paginator
     * @return \Zend\Paginator\Paginator
     */
    public function fetchAll($queryParams = array(),
                             callable $select = null,
                             callable $where = null,
                             $paginator = 'Zend\Paginator\Paginator'
    )
    {

        $entityClass = static::ENTITY_CLASS;

        $parameters = array();
        if ($queryParams && !empty($queryParams['query'])) {
            $parameters['query'] = $queryParams['query'];
        }

        if ($queryParams && !empty($queryParams['orderBy'])) {
            $parameters['orderBy'] = $queryParams['orderBy'];
        }

        // Load the correct queryFactory:
        $objectManager = $this->entityManager;
        /** @var ApigilityFetchAllQuery $fetchAllQuery */
        $fetchAllQuery = new FetchAllOrmQuery();
        if ($select) {
            $fetchAllQuery->setSelectCallback($select);
        }

        // Create collection
        $fetchAllQuery->setObjectManager($objectManager);

        /** @var  \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $fetchAllQuery->createQuery($entityClass, $parameters);

        //allow caller make more decoration
        if ($where) {
            $where($queryBuilder);
        }

        $adapter = $fetchAllQuery->getPaginatedQuery($queryBuilder);
        $reflection = new \ReflectionClass($paginator);
        $collection = $reflection->newInstance($adapter);

        return $collection;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $idOrEntity
     * @param  mixed $data
     * @throws \Perpii\Exception\EntityNotFoundException
     * @return mixed
     */
    public function patch($idOrEntity, $data)
    {
        $entity = $this->getEntity($idOrEntity);
        $originalData = $this->hydrator->extract($entity);
        $patchedData = array_merge($originalData, (array)$data);

        // Hydrate entity
        $this->hydrator->hydrate($patchedData, $entity);
        $this->entityManager->flush();

        return $entity;
    }


    /**
     * Update a resource
     *
     * @param  mixed $idOrEntity
     * @param  mixed $data
     * @throws \Perpii\Exception\EntityNotFoundException
     * @return mixed
     */
    public function update($idOrEntity, $data)
    {
        $entity = $this->getEntity($idOrEntity);
        $this->hydrator->hydrate((array)$data, $entity);
        $this->entityManager->flush();
        return $entity;
    }


    /**
     * @param $idOrEntity
     * @return id or Entity
     * @throws \Perpii\Exception\EntityNotFoundException
     */
    protected function getEntity($idOrEntity)
    {
        $entity = $idOrEntity;
        //get from database if it is id
        if (!is_object($idOrEntity)) {
            $entity = $this->find(static::ENTITY_CLASS, $idOrEntity);
            if (!$entity) {
                throw new EntityNotFoundException($idOrEntity);
            }
        }
        return $entity;
    }

    /**
     * Find entity by ID
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Get base repository
     * @throws \Exception
     * @return \Doctrine\ORM\EntityRepository|null
     */
    protected function getRepository()
    {
        $repository = $this->entityManager->getRepository(static::ENTITY_CLASS);
        if (!$repository) {
            throw new \Exception('Could not found repository for class ' . static::ENTITY_CLASS);
        }
        return $repository;
    }
}