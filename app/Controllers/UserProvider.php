<?php

namespace Controllers;

use Models\Permission;
use Models\Group;
use Models\Message;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Handles all /user routes
 *
 * Class UserProvider
 * @package Controllers
 */
class UserProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get all users
         */
        $controllers->get('/', function (Request $request) use ($app) {

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);

            $users = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->limit($limit)
                ->skip($offset)
                ->getQuery()
                ->execute();

            $users = array_values($users->toArray());
            return $this->getJsonResponseAndSerialize($users, 200, 'user-list');
        })->bind('userList');

        /**
         * Get user by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            if ($id == 'current') {
                $user = $app['user'];
                $jsonGroup = 'user-current';
            } else {
                $jsonGroup = 'user-details';
                $user = $app['doctrine.odm.mongodb.dm']
                    ->createQueryBuilder('Models\\User')
                    ->field('id')
                    ->equals($id)
                    ->getQuery()
                    ->getSingleResult();
            }

            if (!$user) {
                throw new ResourceNotFound();
            }

            if ($id == 'current') {
                $user->setLogoutUrl(
                    $app['url_generator']->generate('logout', array(
                        '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
                    ))
                );
            }

            return $this->getJsonResponseAndSerialize($user, 200, $jsonGroup);
        })->assert('id', '[0-9a-z]+')->bind('userDetail');
        /**
         * Get user by id
         */
        $controllers->get('/{id}/profile', function ($id) use ($app) {
            $user = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\User')
                ->field('id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            if (!$user) {
                throw new ResourceNotFound();
            }

            $profile = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Profile')
                ->field('_id')
                ->equals($user->getProfileId())
                ->getQuery()
                ->getSingleResult();
            if (!$profile) {
                throw new ResourceNotFound();
            }
            return $this->getJsonResponseAndSerialize($profile, 200, 'user-profile');
        })->assert('id', '[0-9a-z]+')->bind('userProfile');
        /**
         * Get groups for user
         */
        $controllers->get('/current/groups', function (Request $request) use ($app) {

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);

            //Get permissionId's for user
            $user = $app['user'];
            if (!$user) {
                throw new AccessDenied();
            }

            $permissionGroupIds = $user->getPermissionGroupIds();

            $groups = $this->app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')->in($permissionGroupIds)
                ->limit($limit)
                ->skip($offset)
                ->getQuery()
                ->execute();

            $groups = array_values($groups->toArray());
            return $this->getJsonResponseAndSerialize($groups, 200, ['group-list'], false);
        })->bind('userGroups');

        /**
         * Get news feed for user
         */
        $controllers->get('/current/news', function (Request $request) use ($app) {

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);
            $allGroupNews = (bool) $request->query->getInt('forAllGroups', 0);

            //Get permissionId's for user
            $user = $app['user'];
            if (!$user) {
                throw new AccessDenied();
            }

            $newsService = new \Service\News($app);
            $news = $newsService->getNewsForUser($user, $limit, $offset, $allGroupNews);

            return $this->getJsonResponseAndSerialize($news, 200, ['group-list', 'board-list'], false);
        })->bind('userNews');

        /**
         * Accept an invite for user
         */
        $controllers->get('/invite/{hash}', function (Request $request, $hash) use ($app) {

            $user = null;

            if ($app['user']) {
                $user = $app['doctrine.odm.mongodb.dm']
                    ->createQueryBuilder('Models\\User')
                    ->field('_id')->equals($app['user']->getId())
                    ->field('invites.hash')->equals($hash)
                    ->getQuery()
                    ->getSingleResult();
            }

            if ($user) {
                //The user object is serialized from the session and needs do be merged with the documentManager for saving
                $user = $app['doctrine.odm.mongodb.dm']->merge($user);

                $groupId = $user->getInviteForHash($hash)->getGroupId();

                $user->setPermissionForGroup($groupId, \Models\Permission::MEMBER);
                $user->removeInviteForHash($hash);
                $app['service.updateSessionUser']($user);

                $app['doctrine.odm.mongodb.dm']->persist($user);
                $app['doctrine.odm.mongodb.dm']->flush();
            } else {
                //TODO: handle errors for users that access the API url and need a nice error page...
                return $app->redirect(
                    $this->app['angular.urlGenerator']->generate('home', array(), UrlGenerator::ABSOLUTE_URL)
                );
            }

            //Redirect the user!
            return $app->redirect(
                $this->app['angular.urlGenerator']->generate('groupDetails', array('id' => $groupId), UrlGenerator::ABSOLUTE_URL)
            );
        })->assert('id', '[0-9a-z]+')->bind('userAcceptInvite');

        return $controllers;
    }
}