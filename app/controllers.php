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

/**
 * Register the custom controllers
 */
$app['account.controller'] = $app->share(function() use ($app) {
    return new Controllers\Account($app);
});

/**
 * Login service providers for home url
 */
$app->get('/', function () use ($app) { return $app->redirect('/login'); });
$app->get('/login', 'account.controller:loginAction')->bind('login');
$app->get('/loginfailed', 'account.controller:loginFailedAction')->bind('loginfailed');

/**
 * Logout service providers
 */
$app->match('/logout', function () {})->bind('logout');


/**
 * Get user by id
 */
$app->get('/user/{id}', function ($id) use ($app) {
    if ($id == 'current') {
        $user = $app['user'];
        $user->setLogoutUrl(
            $app['url_generator']->generate('logout', array(
                '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
            ))
        );
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

    return new Response($app['serializer']->serialize($user, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
})->assert('id', 'alnum');

/**
 * Get groups
 */
$app->get('/group', function () use ($app) {
    $groups = $app['doctrine.odm.mongodb.dm']
        ->getRepository('Models\\Group')
        ->findAll();
    $groups = array_values($groups->toArray());
    return new Response($app['serializer']->serialize($groups, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
});
/**
 * Get group by id
 */
$app->get('/group/{id}', function ($id) use ($app) {
    $group = $app['doctrine.odm.mongodb.dm']
        ->createQueryBuilder('Models\\Group')
        ->field('id')
        ->equals($id)
        ->getQuery()
        ->getSingleResult();

    return new Response($app['serializer']->serialize($group, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
})->assert('id', '[0-9a-z]+');
/**
 * Add group
 */
$app->post('/group', function (Request $request) use ($app){
    $group = $app['serializer']->deserialize($request->getContent(), 'Models\Group', 'json');
    $app['doctrine.odm.mongodb.dm']->persist($group);
    $app['doctrine.odm.mongodb.dm']->flush();
    return new Response('', 201);
});
/**
 * Get group messages
 */
$app->get('/group/{groupId}/message', function ($groupId) use ($app) {
    $messages = $app['doctrine.odm.mongodb.dm']
        ->createQueryBuilder('Models\\Message')
        ->field('groupId')
        ->equals($groupId)
        ->limit(20)
        ->getQuery()
        ->execute();

    $messages = array_values($messages->toArray());
    return new Response($app['serializer']->serialize($messages, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
})->assert('id', 'alnum');
/**
 * Add a message to a group
 */
$app->post('/group/{groupId}/message', function (Request $request, $groupId) use ($app){
    $group = $app['doctrine.odm.mongodb.dm']
        ->createQueryBuilder('Models\\Group')
        ->field('id')
        ->equals($groupId)
        ->getQuery()
        ->getSingleResult();

    if (!$group) {
        throw new ResourceNotFound();
    }

    $message = $app['serializer']->deserialize($request->getContent(), 'Models\Message', 'json');
    $message->setGroupId($groupId);
    $app['doctrine.odm.mongodb.dm']->persist($message);
    $app['doctrine.odm.mongodb.dm']->flush();
    return new Response('', 201);
});

/**
 * Get message by id
 */
$app->get('/message/{id}', function ($id) use ($app) {
    $message = $app['doctrine.odm.mongodb.dm']
        ->createQueryBuilder('Models\\Message')
        ->field('id')
        ->equals($id)
        ->getQuery()
        ->getSingleResult();

    return new Response($app['serializer']->serialize($message, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
})->assert('id', 'alnum');

/**
 * Get all messages
 */
$app->get('/message', function () use ($app) {
    $messages = $app['doctrine.odm.mongodb.dm']
        ->getRepository('Models\\Message')
        ->findAll();
    $messages = array_values($messages->toArray());
    return new Response($app['serializer']->serialize($messages, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
});
/**
 * Post a message in a group
 */
$app->get('/message', function () use ($app) {
    $messages = $app['doctrine.odm.mongodb.dm']
        ->getRepository('Models\\Message')
        ->findAll();
    $messages = array_values($messages->toArray());
    return new Response($app['serializer']->serialize($messages, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
});



/**
 * Get pinboards for group
 */
$app->get('/group/{id}/boards', function ($id) use ($app) {
    $board = null;
    if (!$board) {
        $app->abort(404, "A board for group id $id does not exist.");
    }
    return $app->json($board);
})->assert('id', 'alnum');

/**
 * Get pinboard by id
 */
$app->get('/board/{id}', function ($id) use ($app) {
    $board = null;
    if (!$board) {
        $app->abort(404, "A group with id $id does not exist.");
    }
    return $app->json($board);
})->assert('id', '[0-9]+');