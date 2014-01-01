<?php

namespace Service;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Register Angular service provider as a Silex service provider
 */
class AngularServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     *
     * @param \Silex\Application $app
     */
    public function register(Application $app)
    {
        $app['angular.urlGenerator'] = $app->share(function($app) {
            if ($app['test'] === true) {
                return new Angular\UrlGenerator('test.socializr.io', 'http');
            }
            return new Angular\UrlGenerator();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @param \Silex\Application $app
     */
    public function boot(Application $app)
    {

    }

}