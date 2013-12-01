<?php
/**
 * Created by PhpStorm.
 * User: Sander en Dorien
 * Date: 19-11-13
 * Time: 21:37
 */

/** @ODM\Document */
class Pinboard {
    /** @ODM\Id */
    private $id;

    private $group;
    private $messages;
    private $newsitems;
} 