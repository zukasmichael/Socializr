<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
$app->get('/', function () use ($app) { return $app->redirect('/login'); });
$app->get('/login', 'account.controller:loginAction');
$app->get('/loginfailed', 'account.controller:loginFailedAction');

/**
 * Logout service providers
 */
$app->match('/logout', function () {})->bind('logout');




//
//API STUBS BELOW FOR TESTING:
//


/**
 * Messages stub
 * @param null $id
 * @return array
 */
$getMessage = function($id = null) {
    $messages = [];

    $messages[31]['id'] = 31;
    $messages[31]['title'] = 'First message';
    $messages[31]['content'] = 'Hello y\'all';
    $messages[31]['createdAt'] = '2013-04-11';
    $messages[31]['isSticky'] = false;

    $messages[32]['id'] = 32;
    $messages[32]['title'] = 'Second message';
    $messages[32]['content'] = 'Je moeder';
    $messages[32]['createdAt'] = '2013-05-11';
    $messages[32]['isSticky'] = false;

    $messages[33]['id'] = 33;
    $messages[33]['title'] = 'Third message';
    $messages[33]['content'] = 'Je moeder nog een keer';
    $messages[33]['createdAt'] = '2015-05-11';
    $messages[33]['isSticky'] = false;

    $messages[21]['id'] = 21;
    $messages[21]['title'] = 'Top pin-up';
    $messages[21]['content'] = 'Very important pin';
    $messages[21]['createdAt'] = '2013-04-11';
    $messages[21]['isSticky'] = true;

    $messages[22]['id'] = 22;
    $messages[22]['title'] = 'Interesting pin';
    $messages[22]['content'] = 'Pinnen niet toegestaan';
    $messages[22]['createdAt'] = '2013-05-11';
    $messages[21]['isSticky'] = true;

    if ($id) {
        return $messages[$id];
    }

    return array_values($messages);
};

/**
 * Pinboard stub
 * @param null $id
 * @param null $groupId
 * @return mixed
 */
$getPinboard = function($id = null, $groupId = null) use ($getMessage) {
    $pinBoards[41]['id'] = 41;
    $pinBoards[41]['groupId'] = 51;
    $pinBoards[41]['stickies'][] = $getMessage(21);
    $pinBoards[41]['stickies'][] = $getMessage(22);
    $pinBoards[41]['messages'][] = $getMessage(31);
    $pinBoards[41]['messages'][] = $getMessage(33);

    $pinBoards[42]['id'] = 42;
    $pinBoards[42]['groupId'] = 52;
    $pinBoards[42]['stickies'][] = $getMessage(22);
    $pinBoards[42]['messages'][] = $getMessage(33);
    $pinBoards[42]['messages'][] = $getMessage(31);
    $pinBoards[42]['messages'][] = $getMessage(32);

    if ($id) {
        return $pinBoards[$id];
    } elseif ($groupId) {
        foreach ($pinBoards as $pin) {
            if (isset($pin['groupId'][$groupId])) {
                return $pin;
            }
        }
    }
};

/**
 * Group stub
 * @param null $id
 * @return mixed
 */
$getGroup = function($id = null) use ($getPinboard) {
    $groups[51]['id'] = 41;
    $groups[51]['name'] = 'Zeven spaakse wielen';
    $groups[51]['pinboards'][] = $getPinboard(41);
    $groups[51]['pinboards'][] = $getPinboard(42);
    $groups[51]['members'][] = [];

    $groups[52]['id'] = 42;
    $groups[52]['name'] = 'Zes spaakse wielen';
    $groups[52]['pinboards'][] = $getPinboard(42);
    $groups[51]['members'][] = [];

    if ($id) {
        return $groups[$id];
    }
    return array_values($groups);
};

/**
 * Get group by id
 */
$app->get('/group/{id}', function ($id) use ($app, $getGroup) {
    $group = $app['doctrine.odm.mongodb.dm']
        ->createQueryBuilder('Models\\Group')
        ->field('id')
        ->equals($id)
        ->getQuery()
        ->getSingleResult();

    return new Response($app['serializer']->serialize($group, 'json'), 200, array(
        "Content-Type" => $app['request']->getMimeType('json')
    ));
});

/**
 * Get pinboards for group
 */
$app->get('/group/{id}/boards', function ($id) use ($app, $getPinboard) {
    $board = $getPinboard(null, $id);
    if (!$board) {
        $app->abort(404, "A board for group id $id does not exist.");
    }
    return $app->json($board);
})->assert('id', '[0-9]+');

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
 * Add group
 */
$app->post('/group', function (Request $request) use ($app){
    try{
        $group = $app['serializer']->deserialize($request->getContent(), 'Models\Group', 'json');
        $app['doctrine.odm.mongodb.dm']->persist($group);
        $app['doctrine.odm.mongodb.dm']->flush();
        return new Response('', 201);
    } catch(\Exception $e){
        return new Response($e->getMessage(), 500);
    }

});

/**
 * Get pinboard by id
 */
$app->get('/board/{id}', function ($id) use ($app, $getPinboard) {
    $board = $getPinboard($id);
    if (!$board) {
        $app->abort(404, "A group with id $id does not exist.");
    }
    return $app->json($board);
})->assert('id', '[0-9]+');

/**
 * Get message by id
 */
$app->get('/message/{id}', function ($id) use ($app, $getMessage) {
    $message = $getMessage($id);
    if (!$message) {
        $app->abort(404, "A message with id $id does not exist.");
    }
    return $app->json($message);
})->assert('id', '[0-9]+');

/**
 * Get all messages
 */
$app->get('/message', function () use ($app, $getMessage) {
    return $app->json($getMessage());
})->assert('id', '[0-9]+');