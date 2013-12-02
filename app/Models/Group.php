<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Group
{
    /** @ODM\Id(strategy="AUTO") */
    private $id;
    private $name;
    private $pinboard;
    private $members;
} 