<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
//set_time_limit(360);//6 seconds

$loader = require_once __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$app = new Silex\Application();

if (!empty($_GET['test'])) {
    require __DIR__.'/../resources/config/test.php';
} else {
    require __DIR__.'/../resources/config/dev.php';
}

require __DIR__.'/../app/app.php';
require __DIR__.'/../app/controllers.php';

$app->mount('/installation', new \Controllers\InstallationProvider());

/**
 * Match install route, .htaccess forces usage of the install script
 */
$app->match('/install', function(Request $request) use ($app) {

    $originalRequestUri = $request->query->get('request', '');

    //Show installation intro
    $subRequest = Request::create('/login', 'GET', array(), $request->cookies->all(), array(), $request->server->all());
    if ($request->getSession()) {
        $subRequest->setSession($request->getSession());
    }

    $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
    $loginData = json_decode($response->getContent());

    $responseHtml = '<h1>Socializr installation and db population script</h1>' .
        '<p>This script can:</p>' .
        '<ul><li>Install the api (Empty the db and create a SUPER-ADMIN for the user you log in with.)</li>' .
        '<li>Populate the database with dummy data</li></ul></br>' .
        '<h2><strong>If you want to use the API right now, you need to comment the INSTALLATION rewrites from the file: /vagrant/api/.htaccess</strong></h2>';

    if ($app['user']) {
        $logoutUrl = $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
        ));
        $responseHtml .= '<p>You are currently loggedin and can perform the next actions:</p>' .
            '<a href="/installation/doinstall">EMPTY DATABASE AND INSTALL CURRENT USER AS SUPER ADMIN</a></br></br>' .
            '<a href="/installation/dopopulate">REMOVE ALL DB DATA EXCEPT THE CURRENT USER AND ADD DUMMY DATA</a></br></br>' .
            '<a href="' . $logoutUrl . '">Log out</a>';
    } else {
        $responseHtml .= '<p>You are currently not logged-in! To use this script, log in with facebook, google or twitter.</p>' .
            '<a href="' . $loginData->loginPaths->facebook . '">Log-in with Facebook</a></br>' .
            '<a href="' . $loginData->loginPaths->google . '">Log-in with Google</a></br>' .
            '<a href="' . $loginData->loginPaths->twitter . '">Log-in with Twitter</a>';
    }
    return $responseHtml;
});

$app->run();