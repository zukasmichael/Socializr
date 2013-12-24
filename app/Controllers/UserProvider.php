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
         * Get user by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
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
        })->assert('id', '[0-9a-z]+');

        return $controllers;
    }
}