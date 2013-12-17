<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

abstract class AbstractProvider implements ControllerProviderInterface
{
    protected $app;

    /**
     * @param Application $app
     * @return \Silex\ControllerCollection|void
     */
    public function connect(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @throws \AppException\AccessDenied
     */
    protected function checkLoggedin()
    {
        if ($this->app['user'] != null) {
            throw new AccessDenied();
        }
    }

    /**
     * @param $responseData
     * @param int $statusCode
     * @return Response
     */
    protected function getJsonResponseAndSerialize($responseData, $statusCode = 200)
    {
        return $this->getResponseForJson($this->app['serializer']->serialize($responseData, 'json'), $statusCode);
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