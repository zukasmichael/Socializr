<?php

namespace Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

/**
 * Handles all /pinboard routes
 *
 * Class PinboardProvider
 * @package Controllers
 */
class PinboardProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get ALL pinboards
         * TODO: check permissions and visibility
         */
        $controllers->get('/', function () use ($app) {
            $boards = $app['doctrine.odm.mongodb.dm']
                ->getRepository('Models\\Pinboard')
                ->findAll();
            $boards = array_values($boards->toArray());
            return $this->getJsonResponseAndSerialize($boards);
        });

        /**
         * Get pinboards by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            $board = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('_id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            return $this->getJsonResponseAndSerialize($board);
        })->assert('id', '[0-9a-z]+');

        /**
         * Get board messages
         * GET /board/52aa3011341d4140047b23c6/message?limit=30
         */
        $controllers->get('/{boardId}/message', function (Request $request, $boardId) use ($app) {
            $limit = $request->query->getInt('limit', 20);
            $messages = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Message')
                ->field('boardId')
                ->equals($boardId)
                ->limit($limit)
                ->getQuery()
                ->execute();

            $messages = array_values($messages->toArray());
            return $this->getJsonResponseAndSerialize($messages);
        })->assert('boardId', '[0-9a-z]+');

        /**
         * Add a message to a board
         */
        $controllers->post('/{boardId}/message', function (Request $request, $boardId) use ($app) {

            $user = $this->checkLoggedin();

            $board = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('_id')
                ->equals($boardId)
                ->getQuery()
                ->getSingleResult();

            if (!$board) {
                throw new ResourceNotFound();
            }

            $message = $app['serializer']->deserialize($request->getContent(), 'Models\\Message', 'json');
            $message->setBoardId($boardId);
            $message->setGroupId($board->getGroupId());
            $message->setPostUser($user);
            $message->setCreatedAt(new \DateTime());

            $app['doctrine.odm.mongodb.dm']->persist($message);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($message, 201);
        })->assert('boardId', '[0-9a-z]+');

        return $controllers;
    }
}