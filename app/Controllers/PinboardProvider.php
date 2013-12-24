<?php

namespace Controllers;

use Models\Permission;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use Symfony\Component\Intl\Exception\NotImplementedException;

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
         */
        $controllers->get('/', function (Request $request) use ($app) {
            throw new NotImplementedException('This is not implemented and I guess not needed!');
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

            if (!$board) {
                throw new ResourceNotFound();
            }

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')
                ->equals($board->getGroupId())
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            //Check permissions manually
            $this->checkGroupPermission($group, Permission::READONLY);

            return $this->getJsonResponseAndSerialize($board, 200, 'board-details');
        })->assert('id', '[0-9a-z]+');

        /**
         * Get board messages
         * GET /board/52aa3011341d4140047b23c6/message?limit=30
         */
        $controllers->get('/{boardId}/message', function (Request $request, $boardId) use ($app) {

            //Get limit and offset from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);

            $board = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('_id')
                ->equals($boardId)
                ->getQuery()
                ->getSingleResult();

            if (!$board) {
                throw new ResourceNotFound();
            }

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')
                ->equals($board->getGroupId())
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            //Check permissions manually
            $this->checkGroupPermission($group, Permission::READONLY);

            $messages = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Message')
                ->field('boardId')
                ->equals($boardId)
                ->limit($limit)
                ->skip($offset)
                ->getQuery()
                ->execute();

            $messages = array_values($messages->toArray());

            return $this->getJsonResponseAndSerialize($messages, 200, 'message-list');
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

            return $this->getJsonResponseAndSerialize($message, 201, 'message-details');
        })->assert('boardId', '[0-9a-z]+');

        return $controllers;
    }
}