<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * @ODM\Document(
 *     collection="groups",
 *     indexes={
 *         @ODM\Index(keys={"name"="desc"}, options={"unique"=false}),
 *         @ODM\Index(keys={"description"="desc"}, options={"unique"=true})
 *     }
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class Group
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getName",setter="setName")
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getDescription",setter="setDescription")
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @ODM\ReferenceMany(
     *     targetDocument="\Models\Pinboard",
     *     mappedBy="groupId",
     *     repositoryMethod="findByGroup"
     * )
     * @JMS\Accessor(getter="getBoards",setter="setBoards")
     * @JMS\Readonly
     * @JMS\Type("array")
     */
    private $boards = array();

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getVisibility",setter="setVisibility")
     * @JMS\Type("string")
     */
    private $visibility;

    /**
     * @ODM\ReferenceMany(targetDocument="\Models\Member")
     * @JMS\Exclude
     */
    private $members;

    /**
     * @ODM\Collection
     * @JMS\Accessor(getter="getAdminIds",setter="setAdminIds")
     * @JMS\Exclude
     */
    private $adminIds;

    /**
     * @ODM\ReferenceMany(
     *     targetDocument="\Models\User",
     *     repositoryMethod="findByGroup"
     * )
     * @JMS\Accessor(getter="getAdmins",setter="setAdmins")
     * @JMS\Type("array")
     */
    private $admins;

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
     * @param string $visibility
     * @return \Models\Group
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param \Models\Member $members
     * @return \Models\Group
     */
    public function setMembers($members)
    {
        $this->members = $members;
        return $this;
    }

    /**
     * @return \Models\Member
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param array $admins
     * @return \Models\Group
     */
    public function setAdmins($admins)
    {
        $this->adminIds = array();
        $this->admins = $admins;
        foreach ($admins as $admin) {
            $this->adminIds[] = $admin->getId();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * @param \Models\User $admin
     * @return \Models\Group
     */
    public function addAdmin(User $admin)
    {
        $this->adminIds[] = $admin->getId();
        $this->admins[] = $admin;
        return $this;
    }

    /**
     * @throws \Symfony\Component\Intl\Exception\NotImplementedException
     */
    public function setAdminIds()
    {
        throw new NotImplementedException('Set these id\'s by setting the model.');
    }

    /**
     * @return array
     */
    public function getAdminIds()
    {
        return $this->adminIds;
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
} 