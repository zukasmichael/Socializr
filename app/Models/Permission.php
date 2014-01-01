<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/reference/embedded-mapping.html
 */
class Permission extends BaseModel
{
    const BLOCKED = -1;
    const READONLY = 0;
    const MEMBER = 1;
    const MODERATOR = 3;
    const ADMIN = 5;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"user-current"})
     */
    private $groupId;

    /**
     * @ODM\Int
     * @JMS\Accessor(getter="getAccessLevel",setter="setAccessLevel")
     * @JMS\Type("integer")
     * @JMS\Readonly
     * @JMS\Groups({"user-current"})
     */
    private $accessLevel = self::READONLY;

    /**
     * @param string $id
     * @return \Models\Permission
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
     * @param int $accessLevel
     * @return \Models\Permission
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = (int)$accessLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * Check if the given access level has access
     * @param int $accessLevel
     * @return bool
     */
    public function hasAccess($accessLevel)
    {
        $accessLevel = (int)$accessLevel;
        //When the tested access level is smaller then or equal to the actual level, access is granted.
        return $accessLevel <= $this->getAccessLevel();
    }
} 