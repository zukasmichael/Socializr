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
        return $this->dm->createQueryBuilder('\Models\User')->field('_id')->in($adminIds)->getQuery()->execute();
    }
} 