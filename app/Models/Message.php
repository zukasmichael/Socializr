<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Message
{
    /** @ODM\Id */
    private $id;

    /** @ODM\String */
    private $title;

    /** @ODM\String */
    private $contents;

    /** @ODM\Field(type="timestamp") */
    private $createdAt;
} 