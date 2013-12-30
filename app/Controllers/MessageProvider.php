<?php

namespace Controllers;

use Models\Permission;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

/**
 * Handles all /message routes
 *
 * Class MessageProvider
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
        $controllers->get('/{id}', function (Request $request, $id) use ($app) {
            $message = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Message')
                ->field('_id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            if (!$message) {
                throw new ResourceNotFound();
            }

            $group = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Group')
                ->field('_id')
                ->equals($message->getGroupId())
                ->getQuery()
                ->getSingleResult();

            if (!$group) {
                throw new ResourceNotFound();
            }

            //Check permissions manually
            $this->checkGroupPermission($group, Permission::MEMBER);

            return $this->getJsonResponseAndSerialize($message, 200, 'message-details');
        })->assert('id', '[0-9a-z]+');

        return $controllers;
    }
}