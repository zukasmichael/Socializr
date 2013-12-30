<?php
set_time_limit(360);//6 seconds

$loader = require_once __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$app = new Silex\Application();

require __DIR__.'/../resources/config/dev.php';
require __DIR__.'/../app/app.php';

require __DIR__.'/../app/controllers.php';

if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}