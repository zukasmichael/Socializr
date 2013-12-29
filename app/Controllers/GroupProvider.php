<?php

namespace Controllers;

use Models\Permission;
use Models\Group;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use AppException\ModelInvalid;
use Service\Queue\Invite as InviteService;

/**
 * Handles all /group routes
 *
 * Class GroupProvider
 * @package Controllers
 */
class GroupProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get groups
         */
        $controllers->get('/', function (Request $request) use ($app) {

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);

            //Get permissionId's for user
            $user = $app['user'] ? $app['user'] : $app['anonymous_user'];
            $permissionGroupIds = $user->getPermissionGroupIds();

            //Check permission in query
            $qb = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group');
            $qb->addOr($qb->expr()->field('visibility')->notEqual(Group::VISIBILITY_SECRET));
            if (!empty($permissionGroupIds)) {
                $qb->addOr($qb->expr()->field('_id')->in($permissionGroupIds));
            }

            //Query all groups
            $groups = $qb->limit($limit)
                ->skip($offset)
                ->getQuery()
                ->execute();

            $groups = array_values($groups->toArray());
            return $this->getJsonResponseAndSerialize($groups, 200, 'group-list');
        });

        /**
         * Get group by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($id)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            //Check permissions manually
            $this->checkGroupPermission($group, Permission::READONLY);

            return $this->getJsonResponseAndSerialize($group, 200, 'group-details');
        })->assert('id', '[0-9a-z]+')->bind('groupDetails');

        /**
         * Get group boards
         * GET /group/52aa3011341d4140047b23c6/board?limit=30
         */
        $controllers->get('/{groupId}/board', function (Request $request, $groupId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            //Check permissions manually
            $this->checkGroupPermission($group, Permission::READONLY);

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);

            $boards = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('groupId')->equals($groupId)
                ->limit($limit)
                ->skip($offset)
                ->getQuery()
                ->execute();

            $boards = array_values($boards->toArray());
            return $this->getJsonResponseAndSerialize($boards, 200, 'board-list');
        })->assert('groupId', '[0-9a-z]+')->bind('boardDetails');

        /**
         * Add group
         */
        $controllers->post('/', function (Request $request) use ($app) {

            $user = $this->checkLoggedin();

            $group = $app['serializer']->deserialize($request->getContent(), 'Models\\Group', 'json');

            $app['doctrine.odm.mongodb.dm']->persist($group);
            $app['doctrine.odm.mongodb.dm']->flush();

            try {
                //The user object is serialized from the session and needs do be merged with the documentManager for saving
                $user = $app['doctrine.odm.mongodb.dm']->merge($user);

                $user->setPermissionForGroup($group->getId(), \Models\Permission::ADMIN);
                $app['service.updateSessionUser']($user);

                $app['doctrine.odm.mongodb.dm']->persist($user);
                $app['doctrine.odm.mongodb.dm']->flush();
            } catch (\Exception $e) {
                $app['doctrine.odm.mongodb.dm']->remove($group);
                $app['doctrine.odm.mongodb.dm']->flush();
                throw $e;
            }

            return $this->getJsonResponseAndSerialize($group, 201, 'group-details');
        });

        /**
         * Add a board to a group
         */
        $controllers->post('/{groupId}/board', function (Request $request, $groupId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            $this->checkGroupPermission($group, Permission::MEMBER);

            $board = $app['serializer']->deserialize($request->getContent(), 'Models\\Pinboard', 'json');
            $board->setGroupId($groupId);
            $group->addBoard($board);

            $app['doctrine.odm.mongodb.dm']->persist($board);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($board, 201, 'board-details');
        })->assert('groupId', '[0-9a-z]+');


        /**
         * Invite a user for a group
         */
        $controllers->get('/{groupId}/invite/{userId}', function (Request $request, $groupId, $userId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound('Group does not exist.');
            } else {
                //Check admin permissions manually for current user
                $user = $this->checkGroupPermission($group, Permission::ADMIN);
            }

            $invitedUser = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->field('_id')->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if (!$invitedUser) {
                throw new ResourceNotFound('Invited user does not exist.');
            } elseif ($invitedUser->hasPermissionForGroup($group, Permission::MEMBER)) {
                throw new ModelInvalid('This user already has permission.');
            }

            //Return 200 OK if the invite for the user exists.
            foreach ($invitedUser->getInvites() as $invite) {
                if ($invite->getGroupId() == $groupId) {
                    return $this->getJsonResponseAndSerialize($user, 200, 'user-list');
                }
            }

            //Add new invite to the queue
            $inviteService = new \Service\Queue\Invite($app);
            $inviteService->queueInvite(
                (new \Models\Invite())->setGroupId($groupId),
                $group,
                $invitedUser,
                $user
            );

            return $this->getJsonResponseAndSerialize($user, 202, 'user-list');
        })->assert('groupId', '[0-9a-z]+')->bind('groupInviteUser');


        /**
         * Block a user for a group
         */
        $controllers->get('/{groupId}/block/{userId}', function (Request $request, $groupId, $userId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound('Group does not exist.');
            } else {
                //Check admin permissions manually for current user
                $user = $this->checkGroupPermission($group, Permission::ADMIN);
                if ($user->getId() == $userId) {
                    throw new ModelInvalid('You can\'t block yourself.');
                }
            }

            $blockUser = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->field('_id')->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if (!$blockUser) {
                throw new ResourceNotFound('Block user does not exist.');
            } elseif (!$blockUser->hasPermissionForGroup($group, Permission::MEMBER)) {
                throw new ModelInvalid('This user does is not a member of this group.');
            } elseif ($blockUser->hasPermissionForGroup($group, Permission::ADMIN)) {
                throw new ModelInvalid('Can\'t block an other admin.');
            }

            $blockUser->setPermissionForGroup($group->getId(), Permission::BLOCKED, false);

            $app['doctrine.odm.mongodb.dm']->persist($blockUser);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($blockUser, 200, 'user-list');
        })->assert('groupId', '[0-9a-z]+')->bind('groupBlockUser');


        /**
         * Promote a user for a group
         */
        $controllers->get('/{groupId}/promote/{userId}', function (Request $request, $groupId, $userId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound('Group does not exist.');
            } else {
                //Check admin permissions manually for current user
                $user = $this->checkGroupPermission($group, Permission::ADMIN);
                if ($user->getId() == $userId) {
                    throw new ModelInvalid('You can\'t promote yourself.');
                }
            }

            $promoteUser = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->field('_id')->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if (!$promoteUser) {
                throw new ResourceNotFound('Promote user does not exist.');
            } elseif (!$promoteUser->hasPermissionForGroup($group, Permission::MEMBER)) {
                throw new ModelInvalid('This user does is not a member of this group.');
            } elseif ($promoteUser->hasPermissionForGroup($group, Permission::ADMIN)) {
                throw new ModelInvalid('Can\'t promote an other admin.');
            }

            $promoteUser->setPermissionForGroup($group->getId(), Permission::ADMIN);

            $app['doctrine.odm.mongodb.dm']->persist($promoteUser);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($promoteUser, 200, 'user-list');
        })->assert('groupId', '[0-9a-z]+')->bind('groupPromoteUser');

        return $controllers;
    }
}