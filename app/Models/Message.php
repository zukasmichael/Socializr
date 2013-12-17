<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(
 *     collection="messages",
 *     indexes={
 *         @ODM\Index(keys={"title"="desc"}, options={"unique"=false}),
 *         @ODM\Index(keys={"contents"="desc"}, options={"unique"=true})
 *     }
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Message
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Readonly
     */
    private $groupId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getBoardId",setter="setBoardId")
     * @JMS\Type("string")
     * @JMS\Readonly
     */
    private $boardId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getTitle",setter="setTitle")
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getContents",setter="setContents")
     * @JMS\Type("string")
     */
    private $contents;

    /**
     *  -? ODM\Field(type="timestamp")
     *  -? JMS\Readonly
     * @ODM\String
     * @JMS\Accessor(getter="getCreateAt",setter="setCreateAt")
     * @JMS\Type("string")
     * @TODO auto update in mongodb on creation?
     */
    private $createdAt;

    /**
     * @param mixed $id
     * @return \Models\Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param \Models\Group $group
     */
    public function setGroup(\Models\Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return \Models\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
    /**
     * @param mixed $id
     * @return \Models\Message
     */
    public function setBoardId($id)
    {
        $this->boardId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getBoardId()
    {
        return $this->boardId;
    }

    /**
     * @param string $title
     * @return \Models\Message
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $contents
     * @return \Models\Message
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }
    /**
     * @param string $createdAt
     * @return \Models\Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
} 