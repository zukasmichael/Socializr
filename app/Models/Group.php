<?php
namespace Models;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Created by PhpStorm.
 * User: Sander en Dorien
 * Date: 19-11-13
 * Time: 21:34
 */

/** @ODM\Document */
class Group {
    /** @ODM\Id */
    private $id;
    /** @ODM\String */
    private $name;
    private $pinboard;
    private $members;
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    /**
     * @return mixed
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $pinboard
     */
    public function setPinboard($pinboard)
    {
        $this->pinboard = $pinboard;
    }

    /**
     * @return mixed
     */
    public function getPinboard()
    {
        return $this->pinboard;
    }
} 