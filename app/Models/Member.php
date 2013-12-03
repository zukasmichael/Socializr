<?php
namespace Models;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Member {
    /** @ODM\Id */
    private $id;
    private $firstName;
    private $lastName;
    private $email;
} 