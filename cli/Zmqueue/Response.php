<?php

namespace Zmqueue;

use JMS\Serializer\Serializer;

class Response extends Message
{
    const RESPONSE_STATUS = 'status';
    const RESPONSE_MESSAGE = 'message';
    const RESPONSE_OK = 'OK';
    const RESPONSE_ERROR = 'ERROR';


    protected $status;
    protected $responseMessage;

    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->responseMessage = $message;
    }

    public function jsonSerialize()
    {
        return array(
            self::RESPONSE_STATUS => $this->status,
            self::RESPONSE_MESSAGE => $this->responseMessage
        );
    }

    public static function factory($jsonString)
    {
        $data = parent::getDataArrayForJson($jsonString);

        if (isset($data[self::RESPONSE_STATUS]) && array_key_exists(self::RESPONSE_MESSAGE, $data) === true) {
            return new self($data[self::RESPONSE_STATUS], $data[self::RESPONSE_MESSAGE]);
        }

        throw new ClientException('Can\'t load Zmqueue response data from json string.');
    }
} 