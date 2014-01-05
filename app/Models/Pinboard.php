<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(
 *     collection="boards",
 *     indexes={
 *         @ODM\Index(keys={"id"="desc"}, options={"unique"=true}),
 *         @ODM\Index(keys={"groupId"="desc"}, options={"unique"=false})
 *     },
 *     repositoryClass="\Models\PinboardRepository"
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Pinboard extends BaseModel
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     * @JMS\Groups({"group-list", "group-details", "board-list", "board-details"})
     */
    private $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"group-list", "board-list", "board-details"})
     */
    private $groupId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getTitle",setter="setTitle")
     * @JMS\Type("string")
     * @JMS\Groups({"group-list", "group-details", "board-list", "board-details"})
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
     * @JMS\Groups({"group-details", "board-list", "board-details"})
     */
    private $messages = array();

    /**
     * @ODM\Date
     * @JMS\Readonly
     * @JMS\Groups({"group-list", "group-details", "board-list", "board-details"})
     */
    private $createdAt;

    /**
     * @ODM\Date
     * @JMS\Accessor(getter="getFormattedLastPostAt",setter="setLastPostAt")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"group-list", "group-details", "board-list", "board-details"})
     */
    private $lastPostAt;

    /**
     * @ODM\Int
     * @JMS\Accessor(getter="getVisibility",setter="setVisibility")
     * @JMS\Type("integer")
     * @JMS\Groups({"group-list", "group-details", "board-list", "board-details"})
     *
     * Valid values: [Group::VISIBILITY_OPEN, Group::VISIBILITY_PROTECTED, Group::VISIBILITY_SECRET]
     */
    private $visibility;

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

    /**
     * @param \Models\Message $message
     * @return \Models\Pinboard
     */
    public function addMessage(\Models\Message $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @param string $lastPostAt
     * @return \Models\Pinboard
     */
    public function setLastPostAt($lastPostAt)
    {
        $this->lastPostAt = $lastPostAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastPostAt()
    {
        return $this->lastPostAt;
    }


    /**
     * @return string
     */
    public function getFormattedLastPostAt()
    {
        if (!$this->lastPostAt) {
            return $this->lastPostAt;
        }
        return $this->lastPostAt->format('c');
    }

    /**
     * @param int $visibility
     * @return \Models\Message
     */
    public function setVisibility($visibility)
    {
        $this->visibility = (int)$visibility;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    //TODO: private $newsitems;
} 