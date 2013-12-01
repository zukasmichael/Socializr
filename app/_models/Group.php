<?php
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
    private $name;
    private $pinboard;
    private $members;
} 