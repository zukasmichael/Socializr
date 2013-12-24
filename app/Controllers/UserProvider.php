<?php

namespace Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

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

            $user->setLogoutUrl(
                $app['url_generator']->generate('logout', array(
                    '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
                ))
            );

            return $this->getJsonResponseAndSerialize($user, 200, $jsonGroup);
        })->assert('id', '[0-9a-z]+')->bind('userDetail');

        /**
         * Accept an invite for user
         */
        $controllers->get('/{id}/invite/{hash}', function ($id) use ($app) {
            if ($id == 'current') {
                $user = $app['user'];
            } else {
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

            $user->setLogoutUrl(
                $app['url_generator']->generate('logout', array(
                    '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
                ))
            );

            return $this->getJsonResponseAndSerialize($user, 200, 'user-details');
        })->assert('id', '[0-9a-z]+')->bind('userAcceptInvite');

        return $controllers;
    }
}