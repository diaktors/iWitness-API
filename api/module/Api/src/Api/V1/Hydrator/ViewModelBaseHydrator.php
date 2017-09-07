<?php
namespace Api\V1\Hydrator;

use Zend\Stdlib\Exception\BadMethodCallException;
use Zend\Stdlib\Hydrator\AbstractHydrator;

abstract class ViewModelBaseHydrator extends AbstractHydrator {
    /**
     * Extract values from the provided object
     *
     * Extracts values via the object's getArrayCopy() method.
     *
     * @param  object $object
     * @return array
     * @throws \Zend\Stdlib\Exception\BadMethodCallException for an $object not implementing getArrayCopy()
     */
    public function extract($object)
    {
        $data = $this->getViewModelFields($object);
        $filter = $this->getFilter();

        foreach ($data as $name => $value) {
            if (!$filter->filter($name)) {
                unset($data[$name]);
                continue;
            }
            $extractedName = $this->extractName($name, $object);
            // replace the original key with extracted, if differ
            if ($extractedName !== $name) {
                unset($data[$name]);
                $name = $extractedName;
            }
            $data[$name] = $this->extractValue($name, $value, $object);
        }

        return $data;
    }

    /**
     * Hydrate an object
     *
     * Hydrates an object by passing $data to either its exchangeArray() or
     * populate() method.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     * @throws \Zend\Stdlib\Exception\BadMethodCallException for an $object not implementing exchangeArray() or populate()
     */
    public function hydrate(array $data, $object)
    {
        return parent::hydrate($data, $object);
    }

    /**
     * Get all fields that need for return to client
     *
     * @param  object $object
     * @internal param array $data
     * @return object
     */
    protected abstract function getViewModelFields($object);
}