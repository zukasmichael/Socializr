<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Intl\Exception\NotImplementedException;
use AppException\ModelInvalid;

/**
 * @ODM\Document(
 *     collection="groups",
 *     indexes={
 *         @ODM\Index(keys={"id"="desc"}, options={"unique"=true})
 *     }
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Group extends BaseModel
{
    const VISIBILITY_OPEN = 1;
    const VISIBILITY_PROTECTED = 2;
    const VISIBILITY_SECRET = 3;

    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     * @JMS\Readonly
     * @JMS\Groups({"group-list", "group-details"})
     */
    private $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getName",setter="setName")
     * @JMS\Type("string")
     * @JMS\Groups({"group-list", "group-details"})
     */
    private $name;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getDescription",setter="setDescription")
     * @JMS\Type("string")
     * @JMS\Groups({"group-list", "group-details"})
     */
    private $description;

    /**
     * @ODM\Int
     * @JMS\Accessor(getter="getVisibility",setter="setVisibility")
     * @JMS\Type("integer")
     * @JMS\Groups({"group-list", "group-details"})
     *
     * Valid values: [SELF::VISIBILITY_OPEN, SELF::VISIBILITY_PROTECTED, SELF::VISIBILITY_SECRET]
     */
    private $visibility;

    /**
     * @ODM\ReferenceMany(
     *     targetDocument="\Models\Pinboard",
     *     mappedBy="groupId",
     *     repositoryMethod="findByGroup"
     * )
     * @JMS\Accessor(getter="getBoards",setter="setBoards")
     * @JMS\Type("array")
     * @JMS\Readonly
     * @JMS\MaxDepth(1)
     * @JMS\Groups({"group-list", "group-details"})
     */
    private $boards = array();

    /**
     * @ODM\ReferenceMany(
     *     targetDocument="\Models\Note",
     *     mappedBy="groupId",
     *     repositoryMethod="findByGroup"
     * )
     * @JMS\Accessor(getter="getNotes",setter="setNotes")
     * @JMS\Type("array")
     * @JMS\Readonly
     * @JMS\Groups({"group-details"})
     */
    private $notes = array();

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getHashtag",setter="setHashtag")
     * @JMS\Type("string")
     * @JMS\Groups({"group-details"})
     */
    private $hashtag;
    /**
     * @param mixed $id
     * @return \Models\Group
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
     * @param string $name
     * @return \Models\Group
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     * @return \Models\Group
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $visibility
     * @return \Models\Group
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

    /**
     * @param array $boards
     * @return \Models\Group
     */
    public function setBoards(array $boards)
    {
        $this->boards = $boards;
        return $this;
    }

    /**
     * @param \Models\Pinboard $board
     * @return \Models\Group
     */
    public function addBoard(\Models\Pinboard $board)
    {
        $this->boards[] = $board;
        return $this;
    }

    /**
     * @return array
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * @param array $notes
     * @return \Models\Group
     */
    public function setNotes(array $notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @param \Models\Note $note
     * @return \Models\Group
     */
    public function addNote(\Models\Note $note)
    {
        $this->notes[] = $note;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $hashtag
     * @return \Models\Group
     */
    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashtag()
    {
        return $this->hashtag;
    }

    /**
     * @ODM\PrePersist
     * @ODM\PreUpdate
     */
    public function validate()
    {
        if ($this->getVisibility() < 1 || $this->getVisibility() > 3) {
            throw new ModelInvalid('Visibility for a group must be 1, 2 or 3.');
        }

        $name = $this->getName();
        if (empty($name)) {
            throw new ModelInvalid('A group must have a name.');
        }
    }

    public function unsetAllData()
    {

    }
} 