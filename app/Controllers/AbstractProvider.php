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
     * @throws \AppException\AccessDenied
     * @return \Models\User
     */
    protected function checkLoggedin()
    {
        if ($this->app['user'] === null) {
            throw new AccessDenied();
        }
        return $this->app['user'];
    }

    /**
     * @param $responseData
     * @param int $statusCode
     * @return Response
     */
    protected function getJsonResponseAndSerialize($responseData, $statusCode = 200)
    {
        return $this->getResponseForJson($this->app['serializer']->serialize($responseData, 'json', SerializationContext::create()->enableMaxDepthChecks()), $statusCode);
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