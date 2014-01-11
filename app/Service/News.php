<?php

namespace Service;

use Models\Permission;
use Models\Group;
use Models\Message;

class News
{
    protected $app;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Models\User $user
     * @param $limit
     * @param $offset
     * @param bool $justLastWeek
     * @return array
     */
    public function getNewsForUser(\Models\User $user, $limit, $offset, $justLastWeek = false)
    {
        $app = $this->app;

        $permissionGroupIds = $user->getPermissionGroupIds();

        $messageGroups = $this->getMessageGroups($permissionGroupIds, $limit, $offset, $justLastWeek);
        if (empty($messageGroups)) {
            return [];
        }

        //Get all groups and do an extra check for visibility and permissions
        $groups = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group')
            ->field('_id')->in($messageGroups)
            ->limit($limit)
            ->getQuery()
            ->execute();
        $groups = $groups->toArray();

        //sort the groups by the $messageGroups order
        usort($groups, function ($a, $b) use ($messageGroups) {
            $pos_a = array_search($a->getId(), $messageGroups);
            $pos_b = array_search($b->getId(), $messageGroups);
            return $pos_a - $pos_b;
        });

        $groupIds = [];
        foreach ($groups as $group) {
            $groupIds[] = $group->getId();
        }

        //Check if we still have groups to show messages for
        if (empty($groupIds)) {
            return [];
        }

        //Query three messages per group, sorted on creation date/time
        $messageGroups = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Message')
            ->field('groupId')->in($groupIds)
            ->map('function () {
                    emit(this.groupId, {a:[this]});
                }')
            ->reduce('function (key, values) {
                    result={a:[]};
                    values.forEach( function(v) {
                        result.a = v.a.concat(result.a);
                    } );
                    return result;
                }')
            ->finalize('function (key, value) {
                    Array.prototype.sortAscByProp = function(p){
                       return this.sort(function(a,b){
                         return (a[p] < b[p]) ? 1 : (a[p] > b[p]) ? -1 : 0;
                      });
                    }

                    value.a.sortAscByProp(\'createdAt\');
                    return value.a.slice(0,3);
                }')
            ->getQuery()
            ->execute();

        $boardMessages = [];
        $boardIds = [];
        foreach ($messageGroups as $messages) {
            if (!$messages['_id'] || !$messages['value']) {
                break;
            }
            foreach ($messages['value'] as $message) {
                $msg = Message::populate($app['doctrine.odm.mongodb.dm'], $message);
                $boardId = $msg->getBoardId();

                $boardMessages[$boardId][] = $msg;

                if (!in_array($boardId, $boardIds)) {
                    $boardIds[] = $boardId;
                }
            }
        }

        //get the boards
        $boards = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Pinboard')
            ->field('_id')->in($boardIds)
            ->getQuery()
            ->execute();
        $boards = $boards->toArray();

        $groupBoards = [];
        foreach ($boards as $board) {
            $board->setMessages($boardMessages[$board->getId()]);
            $groupBoards[$board->getGroupId()][] = $board;
        }

        foreach ($groups as $group) {
            $group->setBoards($groupBoards[$group->getId()]);
        }

        return $groups;
    }

    /**
     * @param array $permissionGroupIds
     * @param $limit
     * @param $offset
     * @param $justLastWeek
     * @return array
     */
    public function getMessageGroups(array $permissionGroupIds, $limit, $offset, $justLastWeek)
    {
        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Message');
        $qb->field('groupId')->in($permissionGroupIds);
        if ($justLastWeek) {
            $weekAgo = (new \DateTime())->modify('midnight')->modify('-1 week');
            $qb->field('createdAt')->lt($weekAgo);
        }

        $messages = $qb->sort('createdAt', 'desc')
            ->getQuery()
            ->execute();

        $ids = [];
        $groupIds = [];
        $count = 0;
        foreach ($messages as $message) {
            if (in_array($message->getGroupId(), $ids)) {
                continue;
            }
            //save all id's in array for looping distinct feature
            $ids[] = $message->getGroupId();

            //Manually check offset and limit
            if ($count < $offset) {
                $count++;
                continue;
            } elseif (count($groupIds) == $limit) {
                break;
            }

            //the next group id's can be used
            $groupIds[] = $message->getGroupId();
            $count++;
        }

        return $groupIds;
    }
} 