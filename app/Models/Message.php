<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * @ODM\Document(
 *     collection="messages",
 *     indexes={
 *         @ODM\Index(keys={"id"="desc"}, options={"unique"=true}),
 *         @ODM\Index(keys={"groupId"="desc"}, options={"unique"=false}),
 *         @ODM\Index(keys={"boardId"="desc"}, options={"unique"=false})
 *     },
 *     repositoryClass="\Models\MessageRepository"
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Message extends BaseModel
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getGroupId",setter="setGroupId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $groupId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getBoardId",setter="setBoardId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "message-list", "message-details"})
     */
    private $boardId;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getPostUserId",setter="setPostUserId")
     * @JMS\Type("string")
     * @JMS\Exclude
     */
    private $postUserId;

    /**
     * @ODM\ReferenceOne(
     *     targetDocument="\Models\User",
     *     mappedBy="_id",
     *     repositoryMethod="findOneByMessage"
     * )
     * @JMS\Accessor(getter="getPostUser",setter="setPostUser")
     * @JMS\Type("Models\User")
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $postUser;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getTitle",setter="setTitle")
     * @JMS\Type("string")
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $title;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getContents",setter="setContents")
     * @JMS\Type("string")
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $contents;

    /**
     * @ODM\Date
     * @JMS\Accessor(getter="getFormattedCreateAt",setter="setCreateAt")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
     */
    private $createdAt;

    /**
     * @ODM\Int
     * @JMS\Accessor(getter="getVisibility",setter="setVisibility")
     * @JMS\Type("integer")
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details"})
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
     * @param \Models\User $user
     * @return \Models\Message
     */
    public function setPostUser(\Models\User $user)
    {
        $this->postUserId = $user->getId();
        $this->postUser = $user;
        return $this;
    }

    /**
     * @return \Models\User
     */
    public function getPostUser()
    {
        return $this->postUser;
    }

    /**
     * @throws \Symfony\Component\Intl\Exception\NotImplementedException
     */
    public function setPostUserId()
    {
        throw new NotImplementedException('Set this id by setting the post user.');
    }

    /**
     * @return string
     */
    public function getPostUserId()
    {
        return $this->postUserId;
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @return string
     */
    public function getFormattedCreateAt()
    {
        if (!$this->createdAt) {
            return $this->createdAt;
        }
        return $this->createdAt->format('c');
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
} 