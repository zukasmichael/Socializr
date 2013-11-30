<?php
namespace Socializr\Models;
/**
 * Created by PhpStorm.
 * User: Sander Groen
 * Date: 19-11-13
 * Time: 21:38
 */
/** @ODM\Document */
class Message {
    /** @ODM\Id */
    private $id;
    /** @ODM\String */
    private $title;
    /** @ODM\String */
    private $contents;
    /** @ODM\Field(type="timestamp") */
    private $createdAt;
} 