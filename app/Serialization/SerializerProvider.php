<?php

namespace Serialization;

use JMS\Serializer\SerializerBuilder;
use Silex\Application;
use Silex\ServiceProviderInterface;
use JMS\Serializer\EventDispatcher\EventDispatcher;

/**
 * Register JMS-Serializer as a Silex service provider
 *
 * @author Matthias Adler <macedigital@gmail.com>
 */
class SerializerProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     *
     * @param \Silex\Application $app
     */
    public function register(Application $app)
    {
        $app['serializer'] = $app->share(function($app) {

            $serializer = SerializerBuilder::create()->setDebug(
                isset($app['serializer.debug'])
                    ? (bool) $app['serializer.debug']
                    : $app['debug']
            );

            // jms-serializer will fallback to calling "sys_get_temp_dir()"
            if (isset($app['serializer.cache_dir'])) {
                $serializer->setCacheDir($app['serializer.cache_dir']);
            }


            $serializer->configureListeners(
                function (EventDispatcher $dispatcher) use($app) {
                    $dispatcher->addSubscriber(new PermissionSubscriber($app));
                }
            );

            return $serializer->build();

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
