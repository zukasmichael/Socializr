<?php

use \Auth\Listener\UserProviderListener;

// disable the debug mode
$app['debug'] = false;

// disable the test mode
$app['test'] = false;

// Local
$app['locale'] = 'nl';
$app['session.default_locale'] = $app['locale'];

// Http cache
$app['http_cache.cache_dir'] = __DIR__ . '/../cache/';
$app['http_cache.esi'] = null;

// Doctrine (mongodb)
$app['mongodb.options'] = array(
    'database'   => 'socializr_prod',
    'host'     => 'localhost',
    'port'   => 27017
);

//Monolog settings
$app['log.options'] = array(
    'monolog.logfile' => __DIR__.'/../logs/app.log',
    'monolog.name'    => 'app',
    'monolog.level'   => 300 // = Logger::WARNING
);

//cors config
$app['config.cors'] = array(
    "cors.allowOrigin" => "https://socializr.io",
);

//JMS Serializer options, see: http://jmsyst.com/libs/serializer
// optional: whether to stat cached files or not, defaults to $app['debug']
$app['serializer.debug'] = true;
// optional: defaults to system's default temporary folder
$app['serializer.cache_dir'] = __DIR__ . '/../cache/serializer';

// Login providers
// !! DO NOT COMMIT KEYS !!
$app['login.providers'] = [
    UserProviderListener::SERVICE_FACEBOOK => [
        'API_KEY' => null,
        'API_SECRET' => null
    ],
    UserProviderListener::SERVICE_TWITTER => [
        'API_KEY' => null,
        'API_SECRET' => null
    ],
    UserProviderListener::SERVICE_GOOGLE => [
        'API_KEY' => null,
        'API_SECRET' => null
    ],
    UserProviderListener::SERVICE_GITHUB => [
        'API_KEY' => null,
        'API_SECRET' => null
    ]
];