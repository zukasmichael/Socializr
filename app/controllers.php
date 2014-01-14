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
    $app['anonymous_user'] = new \Models\User();

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
    }
});

/**
 * Service to update user in session
 */
$app['service.updateSessionUser'] = $app->share(function ($app) {
    return function (\Models\User $user) use($app) {
        $token = $app['security']->getToken();

        if (!$token || $app['security.trust_resolver']->isAnonymous($token)) {
            throw new \Exception('Can\'t update a user, authenticate first!');
        }

        $token->setUser($user);
        $app['user'] = $user;
    };
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
$app->get('/', function () use ($app) {
    if ($_SERVER['is_installation']) {
        return null;//do not redirect on installation
    } elseif ($app['test'] === true) {
        return $app->redirect('http://test.socializr.io');
    }
    return $app->redirect('https://socializr.io');
})->bind('home');
$app->get('/login', 'account.controller:loginAction')->bind('login');
$app->get('/loginfailed', 'account.controller:loginFailedAction')->bind('loginFailed');
$app->get('/accountDisabled', 'account.controller:accountDisabledAction')->bind('accountDisabled');
$app->match('/logout', function () {})->bind('logout');

/**
 * These providers handle the designated routes
 * https://speakerdeck.com/simensen/writing-silex-service-providers-and-controller-providers-madison-php
 */
$app->mount('/group', new \Controllers\GroupProvider());
$app->mount('/board', new \Controllers\PinboardProvider());
$app->mount('/message', new \Controllers\MessageProvider());
$app->mount('/user', new \Controllers\UserProvider());
$app->mount('/search', new \Controllers\SearchProvider());
$app->mount('/profiles', new \Controllers\ProfileProvider());
$app->mount('/twitter', new \Controllers\TwitterProvider());
/**
 * Register error handlers
 */
// Handle access denied errors
$app->error(function (\AppException\AccessDenied $e) {
    $message = $e->getMessage() ?: 'Access to this resource is forbidden.';
    return new JsonResponse(array('Message' => $message), 403);
});
// Handle access denied errors
$app->error(function (\AppException\Unauthorized $e) {
    $message = $e->getMessage() ?: 'You can\'t be authorized.';
    return new JsonResponse(array('Message' => $message), 401);
});

// Handle Resource not found errors
$app->error(function (\AppException\ResourceNotFound $e) {
    $message = $e->getMessage() ?: 'The requested resource was not found.';
    return new JsonResponse(array('Message' => $message), 404);
});

// Handle model validation errors
$app->error(function (\AppException\ModelInvalid $e) {
    $message = 'Validation error: ' . $e->getMessage();
    return new JsonResponse(array('Message' => $message), 400);
});

// Handle other exception as 500 errors
$app->error(function (\Exception $e, $code) {
    return new JsonResponse(array('Message' => $e->getMessage()), $code);
});