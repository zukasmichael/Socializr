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
     * @ODM\ReferenceMany(targetDocument="\Models\Member")
     * @JMS\Exclude
     */
    private $members;
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
     * @param string $name
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