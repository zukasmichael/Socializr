<?php

use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;

$app->register(new HttpCacheServiceProvider());

$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../resources/logs/app.log',
    'monolog.name'    => 'app',
    'monolog.level'   => 300 // = Logger::WARNING
));

return $app;