<?php

namespace Models;

class PinboardRepository extends \Doctrine\ODM\MongoDB\DocumentRepository
{
    /**
     * @param \Models\Group $group
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByGroup(\Models\Group $group)
    {
        return $this->findByGroupId($group->getId());
    }

    /**
     * @param $groupId
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByGroupId($groupId)
    {
        return $this->findBy(array('groupId' => $groupId));
    }
} 