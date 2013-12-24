<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/reference/embedded-mapping.html
 */
class Permission
{
    const READONLY = 0;
    const MEMBER = 1;
    const MODERATOR = 3;
    const ADMIN = 5;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Readonly
     */
    private $groupId;

    /**
     * @ODM\Int
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("integer")
     * @JMS\Readonly
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
        return $accessLevel <= $this->getAccessLevel();
    }
} 