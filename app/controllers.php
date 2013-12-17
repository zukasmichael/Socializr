<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

/**
 * Check login before every requests
 */
$app->before(function () use ($app) {
    $token = $app['security']->getToken();
    $app['user'] = null;

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
    }
});

/**
 * Register the custom controllers
 */
$app['account.controller'] = $app->share(function() use ($app) {
    return new Controllers\Account($app);
});

/**
 * Login service providers for home url
 */
$app->get('/', function () use ($app) { return $app->redirect('https://socializr.io'); });
$app->get('/login', 'account.controller:loginAction')->bind('login');
$app->get('/loginfailed', 'account.controller:loginFailedAction')->bind('loginfailed');
$app->match('/logout', function () {})->bind('logout');

/**
 * These providers handle the designated routes
 * https://speakerdeck.com/simensen/writing-silex-service-providers-and-controller-providers-madison-php
 */
$app->mount('/group', new \Controllers\GroupProvider());
$app->mount('/board', new \Controllers\PinboardProvider());
$app->mount('/message', new \Controllers\MessageProvider());

/**
 * Get user by id
 */
$app->get('/user/{id}', function ($id) use ($app) {
    if ($id == 'current') {
        $user = $app['user'];
    } else {
        $user = $app['doctrine.odm.mongodb.dm']
            ->createQueryBuilder('Models\\User')
            ->field('id')
            ->equals($id)
            ->getQuery()
            ->getSingleResult();
    }

    if (!$user) {
        throw new ResourceNotFound();
    }

    $user->setLogoutUrl(
        $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
        ))
    );

    return new Response($app['serializer']->serialize($user, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
})->assert('id', '[0-9a-z]+');


/**
 * Register error handlers
 */
// Handle access denied errors
$app->error(function (\AppException\AccessDenied $e) {
    $message = $e->getMessage() ?: 'Access to this resource is forbidden.';
    return new JsonResponse(array('Message' => $message), 403);
});

// Handle Resource not found errors
$app->error(function (\AppException\ResourceNotFound $e) {
    $message = $e->getMessage() ?: 'The requested resource was not found.';
    return new JsonResponse(array('Message' => $message), 404);
});
// Handle other exception as 500 errors
$app->error(function (\Exception $e, $code) {
    return new JsonResponse(array('Message' => $e->getMessage()), $code);
});