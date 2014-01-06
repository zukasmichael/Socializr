<?php

namespace Controllers;

use Models\Permission;
use Models\Profile;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Handles all /profiles routes
 *
 * Class ProfileProvider
 * @package Controllers
 */
class ProfileProvider extends AbstractProvider{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get ALL profiles
         */
        $controllers->get('/', function (Request $request) use ($app) {
            throw new NotImplementedException('This is not implemented and I guess not needed!');
        });

        /**
         * Get profile by id
         */
        $controllers->get('/{id}', function ($id) use ($app) {
            $profile = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Profile')
                ->field('_id')
                ->equals($id)
                ->getQuery()
                ->getSingleResult();

            if (!$profile) {
                throw new ResourceNotFound();
            }
            return $this->getJsonResponseAndSerialize($profile, 200, 'profile-detail');
        })->assert('id', '[0-9a-z]+')->bind('profileDetail');
        /**
         * Update a profile
         */
        $controllers->post('/{profileId}', function (Request $request, $profileId) use ($app) {
            $profileObj = $app['serializer']->deserialize($request->getContent(), 'Models\\Profile', 'json');

            $profile = $app['doctrine.odm.mongodb.dm']
                ->createQueryBuilder('Models\\Profile')
                ->field('_id')
                ->equals($profileId)
                ->getQuery()
                ->getSingleResult();

            if (!$profile) {
                throw new ResourceNotFound();
            }
            $profile->setInterests($profileObj->getInterests());

            $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s+', $profileObj->getBirthday());
            $profile->setBirthday($birthday);

            $profile->setAbout($profileObj->getAbout());

            $app['doctrine.odm.mongodb.dm']->persist($profile);
            $app['doctrine.odm.mongodb.dm']->flush();

            return $this->getJsonResponseAndSerialize($profile, 201, 'profile-update');
        })->assert('boardId', '[0-9a-z]+');

        return $controllers;
    }
} 