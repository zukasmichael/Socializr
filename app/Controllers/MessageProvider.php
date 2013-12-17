<?php

namespace Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

/**
 * Handles all /group routes
 *
 * Class GroupProvider
 * @package Controllers
 */
class MessageProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get message by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            $message = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Message')
                ->field('_id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            return $this->getJsonResponseAndSerialize($message);
        })->assert('id', '[0-9a-z]+');

        return $controllers;
    }
}