<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

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
     * @ODM\ReferenceMany(targetDocument="\Models\Pinboard")
     * @JMS\Exclude
     */
    private $pinboards;

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
     * @ODM\ReferenceMany(targetDocument="\Models\User")
     * @JMS\Accessor(getter="getAdmins",setter="setAdmins")
     * @JMS\Type("array")
     */
    private $admins;

    /**
     * @ODM\ReferenceMany(targetDocument="\Models\Message")
     * @JMS\Accessor(getter="getMessages",setter="setMessages")
     * @JMS\Type("array")
     */
    private $messages;

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
        $this->admins = $admins;
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
     * @param array $messages
     * @return \Models\Group
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }
    /**
     * @return array
     */
    public function getMessages(){
        return $this->messages;
    }

    /**
     * @param \Models\User $admin
     * @return \Models\Group
     */
    public function addAdmin(User $admin)
    {
        $this->admins[] = $admin;
        return $this;
    }

    /**
     * @param array $pinboards
     * @return \Models\Group
     */
    public function setPinboards(array $pinboards)
    {
        $this->pinboards = $pinboards;
        return $this;
    }

    /**
     * @param \Models\Pinboard $pinboard
     * @return \Models\Group
     */
    public function addPinboard(\Models\Pinboard $pinboard)
    {
        $this->pinboards[] = $pinboard;
        return $this;
    }

    /**
     * @return string
     */
    public function getPinboards()
    {
        return $this->pinboards;
    }
} 