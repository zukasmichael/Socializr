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
                ->field('id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            return $this->getJsonResponseAndSerialize($message);
        })->assert('id', '[0-9a-z]+');

        /**
         * Get all messages by group id
         */
        $controllers->get('/{groupId}', function ($groupId) use ($app) {
            return null;
        })->assert('groupId', '[0-9]+');

        /**
         * Post a message
         */
        $controllers->post('/', function (Request $request) use ($app) {

            $this->checkLoggedin();

            $message = $app['serializer']->deserialize($request->getContent(), 'Models\Message', 'json');
            $app['doctrine.odm.mongodb.dm']->persist($message);
            $app['doctrine.odm.mongodb.dm']->flush();
            return new Response('', 201);
        });

        return $controllers;
    }
}