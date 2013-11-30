<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Models\Group;

$app->get('/', function () use ($app) {
	return 'Hello world';
})
->bind('homepage');

$app->get('/group', function() use ($app) {
    $groups = $app['doctrine.odm.mongodb.dm']
        ->getRepository('Models\Group')
        ->findAll();
    return new JsonResponse($groups);
});

$app->get('/group/{id}', function() use ($app){

});

$app->post('/group', function (Request $request) use ($app){
    $group = new Models\Group();
    $obj = json_decode($request->getContent());
    $group->setName($obj->name);
    $app['doctrine.odm.mongodb.dm']->persist($group);
    $app['doctrine.odm.mongodb.dm']->flush();
    return new Response('', 201);
});
$app->put('/group', function(Request $request){

});