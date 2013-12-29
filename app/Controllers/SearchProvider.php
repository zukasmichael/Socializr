<?php

namespace Controllers;

use Models\Group;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use JMS\Serializer\SerializationContext;

/**
 * Handles all /search routes
 *
 * Class SearchProvider
 * @package Controllers
 */
class SearchProvider extends AbstractProvider
{
    const TYPE_USERS = 'users';
    const TYPE_GROUPS = 'groups';
    const TYPE_BOARDS = 'boards';
    const TYPE_ALL = '';

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
        $controllers->get('/{query}', function (Request $request, $query) use ($app) {

            //Get parameters from request
            $limit = $request->query->getInt('limit', 20);
            $offset = $request->query->getInt('offset', 0);
            $type = $request->query->getAlpha('type', self::TYPE_ALL);

            $jsonResults = '{%s, %s}';

            //Find users
            $jsonUsers = '"' . self::TYPE_USERS . '" : %s';
            $users = [];
            if ($this->isType($type, self::TYPE_USERS)) {
                $users = $this->findUsers($query, $limit, $offset);
            }
            $jsonUsers = sprintf($jsonUsers, $this->serializeJson($users, 'user-list'));

            //Find groups
            $jsonGroups = '"' . self::TYPE_GROUPS . '" : %s';
            $groups = [];
            if ($this->isType($type, self::TYPE_GROUPS)) {
                $groups = $this->findGroups($query, $limit, $offset);
            }
            $jsonGroups = sprintf($jsonGroups, $this->serializeJson($groups, 'group-list'));

            //Combine json results
            $results = sprintf($jsonResults,
                $jsonUsers,
                $jsonGroups
            );

            return $this->getResponseForJson($results, 200);
        });

        return $controllers;
    }

    protected function findUsers($query, $limit, $offset)
    {
        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User');
        $qb->addOr($qb->expr()->field('title')->equals(new \MongoRegex("/.*$query.*/i")));
        $qb->addOr($qb->expr()->field('description')->equals(new \MongoRegex("/.*$query.*/i")));

        $users = $qb->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute();

        return array_values($users->toArray());
    }

    protected function findGroups($query, $limit, $offset)
    {
        //Get permissionId's for user
        $user = $this->app['user'] ? $this->app['user'] : $this->app['anonymous_user'];
        $permissionGroupIds = $user->getPermissionGroupIds();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group');

        //Get the permission expression
        //$permExpr = $qb->expr()->field('visibility')->notEqual(Group::VISIBILITY_SECRET);
        $permExpr= $qb->expr()->addOr($qb->expr()->field('visibility')->notEqual(Group::VISIBILITY_SECRET));
        if (!empty($permissionGroupIds)) {
            $permExpr->addOr($qb->expr()->field('_id')->in($permissionGroupIds));
        }

        //Query the different fields
        $qb->addOr(
            $qb->expr()->field('name')->equals(new \MongoRegex("/.*$query.*/i"))->addAnd($permExpr)
        );
        $qb->addOr(
            $qb->expr()->field('description')->equals(new \MongoRegex("/.*$query.*/i"))->addAnd($permExpr)
        );

        $groups = $qb->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute();

        return array_values($groups->toArray());
    }

    /**
     * @param $data
     * @param null $groups
     * @param bool $enableDepthChecks
     * @return Response
     */
    protected function serializeJson($data, $groups = null, $enableDepthChecks = true)
    {
        $serializeContext = $enableDepthChecks ? SerializationContext::create()->enableMaxDepthChecks() : SerializationContext::create();
        if (!empty($groups)) {
            $serializeContext->setGroups($groups);
        }

        return $this->app['serializer']->serialize($data, 'json', $serializeContext);
    }

    protected function isType($value, $type)
    {
        return $value == self::TYPE_ALL || (
            $value == $type && (
                $value == self::TYPE_BOARDS ||
                $value == self::TYPE_GROUPS ||
                $value == self::TYPE_USERS
            )
        );
    }
}