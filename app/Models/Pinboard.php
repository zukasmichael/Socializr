<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(
 *     collection="boards",
 *     repositoryClass="\Models\PinboardRepository"
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Pinboard
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
     * @JMS\Accessor(getter="getTitle",setter="setTitle")
     * @JMS\Type("string")
     */
    private $title;


    /**
     * @ODM\ReferenceMany(
     *     targetDocument="\Models\Message",
     *     repositoryMethod="findByBoard"
     * )
     * @JMS\Accessor(getter="getMessages",setter="setMessages")
     * @JMS\Type("array")
     * @JMS\Readonly
     */
    private $messages = array();

    /**
     * @ODM\Field(type="timestamp")
     * @JMS\Readonly
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
     * @param mixed $id
     * @return \Models\Message
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
     * @param string $title
     * @return \Models\Message
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $messages
     * @return \Models\Group
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    //TODO: private $newsitems;
} 