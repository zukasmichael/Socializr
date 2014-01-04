<?php
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

set_time_limit(360);//6 seconds

$loader = require_once __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$app = new Silex\Application();

require __DIR__.'/../resources/config/test.php';

ini_set('session.cookie_domain', '.socializr.io');
session_name('test_socializr_sess');
session_set_cookie_params(0, '/', '.socializr.io');

require __DIR__.'/../app/app.php';

require __DIR__.'/../app/controllers.php';

/**
 * Match install route, .htaccess forces usage of the install script
 */
$app->match('/reset/{param}', function(Request $request, $param) use ($app) {
    $installProvider = new \Controllers\InstallationProvider();
    $installProvider->connect($app);

    $secondUser = 'srooijde@twitter.com';

    if ($param == 'nouser') {
        $installProvider->populateWithNoUser();
    }

    if ($param == 'foradmin') {
        $installProvider->populateWithAdmin($secondUser);
    }

    if ($param == 'loginseconduser') {
        $user = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User')
            ->field('email')->equals($secondUser)
            ->getQuery()
            ->getSingleResult();
        if (!$user) {
            throw new \AppException\ResourceNotFound();
        }
        $app['service.updateSessionUser']($user);
    }

    if ($param == 'loginsuperadmin') {
        $user = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User')
            ->field('roles')->all(array('ROLE_SUPER_ADMIN'))
            ->getQuery()
            ->getSingleResult();
        if (!$user) {
            throw new \AppException\ResourceNotFound();
        }
        $app['service.updateSessionUser']($user);
    }

    return new JsonResponse(['result' => 'OK']);
});

$app->run();