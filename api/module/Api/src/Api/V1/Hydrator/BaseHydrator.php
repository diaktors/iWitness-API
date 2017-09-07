<?php
namespace Api\V1\Hydrator;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DoctrineModule\Stdlib\Hydrator\Filter\PropertyName;
use Perpii\Util\String;
use ZF\Rest\AbstractResourceListener;

abstract class BaseHydrator extends DoctrineObject
{
    const HYDRATOR = 'Hydrator';

    /** @var  AbstractResourceListener $resource */
    private $resource;

    public function __construct(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        parent::__construct($entityManager, true);
        $this->resource = $resource;
        $this->init();
    }

    /**
     * Build filter base on default and user input data on query string
     */
    protected function init()
    {
        $defaultFields = $this->getDefaultFields();
        $filterFields = $this->getFilteredFields();
        $actualFields = array('id');

        if (!$filterFields || count($filterFields) <= 0) {
            $actualFields = $actualFields + $defaultFields;
        } else {
            foreach ($defaultFields as $field) {
                if (in_array($field, $filterFields)) {
                    $actualFields[] = $field;
                }
            }
        }

        $propertyNameFilter = new PropertyName($actualFields, false);
        $this->addFilter('PropertyName', $propertyNameFilter);
    }

    /**
     * @return string  array
     */
    protected function getFilteredFields()
    {
        $filteredFields = array();
        if ($this->resource) {
            $resourceClass = $this->getResourceClass();
            $hydratorForClass = $this->getHydratorFor(get_called_class());

            $isCurrentResource = $resourceClass == $hydratorForClass;

            $fields = $this->getQueryParameterArray('fields');
            if (count($fields) > 0) {
                foreach ($fields as $field) {
                    $field = trim($field);
                    if (strpos($field, '.') == false) {
                        if ($isCurrentResource) {
                            $filteredFields[] = $field;
                        }
                    } else {
                        if (String::startsWith($field, $hydratorForClass . ".")) {
                            $filteredFields[] = $this->extractClassName($field, '.');
                        }
                    }
                } //foreach
            }
        }
        return $filteredFields;
    }

    /**
     * @param $name
     * @return null | string
     */
    private function  getQueryParameter($name)
    {
        $parameterValue = null;
        $event = $this->getResource()->getEvent();

        if ($event) {
            /** @var \Zend\Stdlib\Parameters $inputParameters */
            $parameters = $event->getRequest()->getQuery()->toArray();
            if (isset($parameters[$name])) {
                $parameterValue = $parameters[$name];
            }
        }
        return $parameterValue;
    }

    /**
     * @param $name
     * @return array
     */
    private function  getQueryParameterArray($name)
    {
        $params = array();
        $parameter = $this->getQueryParameter($name);
        if (!empty($parameter)) {
            $params = explode(',', $parameter);
            $params = array_map('trim', $params);
        }
        return $params;
    }


    /**
     * @return array
     */
    protected function getExtendFields()
    {
        return $this->getQueryParameterArray('extends');
    }


    /**
     * @return string
     */
    protected function getResourceClass()
    {
        $resourceClass = $this->getResource()->getEntityClass();
        $resourceClass = $this->extractClassName($resourceClass);
        return $resourceClass;
    }

    /**
     * Get Resource name of current hydrator for
     * @param $class
     * @return string
     */
    protected function getHydratorFor($class)
    {
        $class = $this->extractClassName($class);
        if (String::endsWith($class, self::HYDRATOR)) {
            $class = substr($class, 0, strlen($class) - strlen(self::HYDRATOR));
        }
        return $class;
    }


    /**
     * @return AbstractResourceListener
     */
    protected function  getResource()
    {
        return $this->resource;
    }

    /**
     * @return string array
     */
    abstract protected function   getDefaultFields();

    /**
     * @param $classWithNamespace
     * @param string $separator
     * @return string
     */
    private function extractClassName($classWithNamespace, $separator = '\\')
    {
        if (strpos($classWithNamespace, $separator) === false) {
            return $classWithNamespace;
        }
        $from = strrpos($classWithNamespace, $separator) + 1;
        $length = strlen($classWithNamespace) - $from;
        $class = substr($classWithNamespace, $from, $length);
        return $class;
    }
} 