<?php

namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Pinboard
{
    /** @ODM\Id */
    private $id;

    private $group;
    private $messages;
    private $newsitems;
} 