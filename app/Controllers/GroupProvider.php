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
class GroupProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        parent::connect($app);

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        /**
         * Get groups
         */
        $controllers->get('/', function () use ($app) {
            $groups = $app['doctrine.odm.mongodb.dm']
                ->getRepository('Models\\Group')
                ->findAll();
            $groups = array_values($groups->toArray());
            return $this->getJsonResponseAndSerialize($groups);
        });

        /**
         * Get group by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            return $this->getJsonResponseAndSerialize($group);
        })->assert('id', '[0-9a-z]+');

        /**
         * Get group boards
         * GET /group/52aa3011341d4140047b23c6/board?limit=30
         */
        $controllers->get('/{groupId}/board', function (Request $request, $groupId) use ($app) {
            $limit = $request->query->getInt('limit', 20);
            $boards = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('groupId')
                ->equals($groupId)
                ->limit($limit)
                ->getQuery()
                ->execute();

            $boards = array_values($boards->toArray());
            return $this->getJsonResponseAndSerialize($boards);
        })->assert('groupId', '[0-9a-z]+');

        /**
         * Add group
         */
        $controllers->post('/', function (Request $request) use ($app) {

            $this->checkLoggedin();

            $group = $app['serializer']->deserialize($request->getContent(), 'Models\Group', 'json');
            $app['doctrine.odm.mongodb.dm']->persist($group);
            $app['doctrine.odm.mongodb.dm']->flush();

            return new Response('', 201);
        });

        /**
         * Add a board to a group
         */
        $controllers->post('/{groupId}/board', function (Request $request, $groupId) use ($app) {

            $this->checkLoggedin();

            $board = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Pinboard')
                ->field('groupId')
                ->equals($groupId)
                ->getQuery()
                ->getSingleResult();

            if (!$board) {
                throw new ResourceNotFound();
            }

            $board = $app['serializer']->deserialize($request->getContent(), 'Models\\Pinboard', 'json');
            $board->setGroupId($groupId);
            $app['doctrine.odm.mongodb.dm']->persist($board);
            $app['doctrine.odm.mongodb.dm']->flush();

            return new Response('', 201);
        })->assert('boardId', '[0-9a-z]+');

        return $controllers;
    }
}