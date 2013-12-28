<?php

namespace Zmqueue;

use JMS\Serializer\Serializer;

class Request extends Message
{
    const WORKER_NAME = 'worker';
    const WORKER_DATA = 'workerData';

    protected $worker;
    protected $workerData = array();

    public function __construct($worker, array $data)
    {
        $this->worker = $worker;
        $this->workerData = $data;
    }

    public function getWorker()
    {
        return $this->worker;
    }

    public function getWorkerData()
    {
        return $this->workerData;
    }

    public function jsonSerialize()
    {
        return array(
            self::WORKER_NAME => $this->worker,
            self::WORKER_DATA => $this->workerData
        );
    }

    public static function factory($jsonString)
    {
        $data = parent::getDataArrayForJson($jsonString);

        if (isset($data[self::WORKER_NAME]) && array_key_exists(self::WORKER_DATA, $data) === true) {
            return new self($data[self::WORKER_NAME], $data[self::WORKER_DATA]);
        }

        throw new ServerException('Can\'t load Zmqueue request data from json string.');
    }
} 