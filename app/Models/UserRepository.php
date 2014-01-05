<?php

namespace Models;

class UserRepository extends \Doctrine\ODM\MongoDB\DocumentRepository
{
    /**
     * @param \Models\Group $group
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByGroup(\Models\Group $group)
    {
        return $this->findByAdminIds($group->getAdminIds());
    }

    /**
     * @param array $adminIds
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByAdminIds(array $adminIds)
    {
        $adminIds = (array)$adminIds;
        return $this->dm->createQueryBuilder('\Models\User')->field('_id')->in($adminIds)->getQuery()->execute();
    }

    /**
     * @param Message $message
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findOneByMessage(\Models\Message $message)
    {
        return $this->findOneById($message->getPostUserId());
    }

    /**
     * @param Note $note
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findOneByNote(\Models\Note $note)
    {
        return $this->findOneById($note->getPostUserId());
    }

    /**
     * @param string $userId
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findOneById($userId)
    {
        return $this->findOneBy(array('_id' => $userId));
    }
} 