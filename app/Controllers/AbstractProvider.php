<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

use AppException\AccessDenied;
use AppException\ResourceNotFound;

abstract class AbstractProvider implements ControllerProviderInterface
{
    protected $app;

    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $this->app = $app;
        return $this->app['controllers_factory'];
    }

    /**
     * @param bool $throwException
     * @return mixed
     * @throws \AppException\AccessDenied
     */
    protected function checkLoggedin($throwException = true)
    {
        if ($this->app['user'] === null && $throwException) {
            throw new AccessDenied();
        }
        return $this->app['user'];
    }

    /**
     * @param \Models\Group $group
     * @param int $accessLevel
     * @return \Models\User
     * @throws \AppException\AccessDenied
     */
    protected function checkGroupPermission(\Models\Group $group, $accessLevel)
    {
        if ($accessLevel == \Models\Permission::READONLY && $group->getVisibility() === \Models\Group::VISIBILITY_OPEN) {
            return true;
        }

        $user = $this->checkLoggedin();

        if (!$user->hasPermissionForGroup($group, $accessLevel)) {
            throw new AccessDenied('You do not have the correct group permissions.');
        }
        return $user;
    }

    /**
     * @param $responseData
     * @param int $statusCode
     * @param null $groups
     * @param bool $enableDepthChecks
     * @return Response
     */
    protected function getJsonResponseAndSerialize($responseData, $statusCode = 200, $groups = null, $enableDepthChecks = true)
    {
        $serializeContext = $enableDepthChecks ? SerializationContext::create()->enableMaxDepthChecks() : SerializationContext::create();
        if (!empty($groups)) {
            $serializeContext->setGroups($groups);
        }

        return $this->getResponseForJson($this->app['serializer']->serialize($responseData, 'json', $serializeContext), $statusCode);
    }

    /**
     * @param $jsonString
     * @param int $statusCode
     * @return Response
     */
    protected function getResponseForJson($jsonString, $statusCode = 200)
    {
        return new Response($jsonString, $statusCode, array(
            "Content-Type" => $this->app['request']->getMimeType('json')
        ));
    }
} 