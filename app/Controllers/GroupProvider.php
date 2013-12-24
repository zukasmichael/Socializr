<?php

namespace Controllers;

use Models\Permission;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

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
            $qb->addOr($qb->expr()->field('visibility')->notEqual(\Models\Group::VISIBILITY_SECRET));
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
                ->field('_id')
                ->equals($id)
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
                ->field('_id')
                ->equals($groupId)
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
                ->field('groupId')
                ->equals($groupId)
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
                ->field('_id')
                ->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            $this->checkGroupPermission($group, Permission::MEMBER);

            $board = $app['serializer']->deserialize($request->getContent(), 'Models\\Pinboard', 'json');
            $board->setGroupId($groupId);
            $group->addBoard($board);

            $app['doctrine.odm.mongodb.dm']->persist($group);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($board, 201, 'board-details');
        })->assert('groupId', '[0-9a-z]+');


        /**
         * Invite a user for a group
         */
        $controllers->get('/{groupId}/invite/{userId}', function (Request $request, $groupId, $userId) use ($app) {

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')
                ->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            $invitedUser = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->field('_id')
                ->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if (!$group || !$invitedUser) {
                throw new ResourceNotFound();
            }

            //Check admin permissions manually for current user
            $user = $this->checkGroupPermission($group, Permission::ADMIN);

            foreach ($invitedUser->getInvites() as $invite) {
                if ($invite->getGroupId() == $groupId) {
                    return $this->getJsonResponseAndSerialize($user, 202, 'user-list');
                }
            }

            $hash = sha1(openssl_random_pseudo_bytes(32));

            $acceptUri = $app['url_generator']->generate('userAcceptInvite', array(
                'id' => $invitedUser->getId(),
                'hash' => $hash
            ), UrlGenerator::ABSOLUTE_URL);
            $groupUri = $app['url_generator']->generate('groupDetails', array(
                'id' => $group->getId()
            ), UrlGenerator::ABSOLUTE_URL);

            $mailContents = $this->getMailContent('invite', array(
                '%%SENDERUSERNAME%%' => $user->getUserName(),
                '%%GROUPNAME%%' => $group->getName(),
                '%%GROUPURI%%' => $groupUri,
                '%%ACCEPTURI%%' => $acceptUri
            ));

            //Send e-mail to user with invite for group
            $message = \Swift_Message::newInstance()
                ->setSubject('Socializr - U bent uitgenodigd voor een nieuwe groep!')
                ->setFrom('socializr.io@gmail.com')
                ->setTo($invitedUser->getEmail())
                ->setBody($mailContents)
                ->setContentType("text/html");
            $app['mailer']->send($message);

            return $this->getJsonResponseAndSerialize($user, 202, 'user-list');
        })->assert('groupId', '[0-9a-z]+')->bind('groupInviteUser');

        return $controllers;
    }
}