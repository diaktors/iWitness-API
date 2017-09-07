<?php


namespace Api\V1\Service;


interface BackgroundProcessInterface extends ServiceInterface
{

    /**
     * @param $id
     * @param bool $force
     * @return bool
     */
    public function  process($id, $force = true);

    /**
     * @param $max
     * @return int mixed
     */
    public function fetchForProcessing($max);

    /**
     * @param $id
     * @return mixed
     */
    public function markProcessingError($id);

    /**
     * @param $id
     * @return mixed
     */
    public function markProcessingSuccess($id);

    /**
     * @param $id
     * @return mixed
     */
    // public function markEventForProcessing($id);
} 