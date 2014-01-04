<?php

namespace Models;


class ProfileRepository extends \Doctrine\ODM\MongoDB\DocumentRepository {
    /**
     * @param \Models\User $user
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByUser(\Models\User $user)
    {
        return $this->findByUserId($user->getId());
    }

    /**
     * @param $userId
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function findByUserId($userId)
    {
        return $this->findBy(array('userId' => $userId));
    }
} 