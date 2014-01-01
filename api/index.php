<?php
set_time_limit(360);//6 seconds

$loader = require_once __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$app = new Silex\Application();

require __DIR__.'/../resources/config/dev.php';

ini_set('session.cookie_domain', '.socializr.io');
session_name('socializr_sess');
session_set_cookie_params(0, '/', '.socializr.io');

require __DIR__.'/../app/app.php';

require __DIR__.'/../app/controllers.php';

if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}