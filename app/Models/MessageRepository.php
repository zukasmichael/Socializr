<?php

namespace Models;

class MessageRepository extends \Doctrine\ODM\MongoDB\DocumentRepository
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

    /**
     * @param \Models\Pinboard $board
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByBoard(\Models\Pinboard $board)
    {
        return $this->findByGroupId($board->getId());
    }

    /**
     * @param $boardId
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByBoardId($boardId)
    {
        return $this->findBy(array('boardId' => $boardId));
    }
} 