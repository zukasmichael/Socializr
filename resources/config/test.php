<?php

require __DIR__.'/dev.php';

// force the debug mode
$app['debug'] = true;

// force the test mode
$app['test'] = true;

// Doctrine (mongodb)
$app['mongodb.options'] = array(
    'database'   => 'socializr_test',
    'host'     => 'localhost',
    'port'   => 27017
);

//Monolog settings
$app['log.options'] = array(
    'monolog.logfile' => __DIR__.'/../logs/app-test.log',
    'monolog.name'    => 'app',
    'monolog.level'   => 100 // = Logger::DEBUG
);

//cors config
$app['config.cors'] = array(
    "cors.allowOrigin" => "http://test.socializr.io",
);