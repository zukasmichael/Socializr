<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/reference/embedded-mapping.html
 */
class Invite extends BaseModel
{
    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Groups({"user-current"})
     */
    private $groupId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getHash",setter="setHash")
     * @JMS\Type("string")
     * @JMS\Groups({"user-current"})
     */
    private $hash;

    /**
     * @param string $id
     * @return \Models\Invite
     */
    public function setGroupId($id)
    {
        $this->groupId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string $hash
     * @return \Models\Invite
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
} 