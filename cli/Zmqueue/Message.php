<?php

namespace Zmqueue;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

abstract class Message
{
    protected $serializer;

    abstract public function jsonSerialize();

    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param null $groups
     * @return mixed
     */
    public function getJson($groups = null)
    {
        $data = $this->jsonSerialize();

        if (!$this->serializer) {
            return json_encode($data);
        }

        $serializeContext = SerializationContext::create()->enableMaxDepthChecks();
        if (!empty($groups)) {
            $serializeContext->setGroups($groups);
        }

        return $this->serializer->serialize($data, 'json', $serializeContext);
    }

    protected static function getDataArrayForJson($jsonString)
    {
        $data = json_decode($jsonString);

        if ($data instanceof \stdClass) {
            $data = self::objectToArray($data);
        }

        return $data;
    }

    protected static function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * for recursive call
            */
            return array_map(array('\\Zmqueue\\Message','objectToArray'), $d);
        }
        else {
            // Return array
            return $d;
        }
    }
} 