<?php

$loader = require_once __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

use Zmqueue\Server;
use Zmqueue\ServerException;
use Zmqueue\Request;
use Zmqueue\Response;
use Zmqueue\Worker;

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Neutron\Silex\Provider\MongoDBODMServiceProvider;
use Serialization\SerializerProvider;

/**
 * Start a new Silex Application
 */
$app = new Silex\Application();
require __DIR__.'/../resources/config/dev.php';

//Set-up logging
$logOptions = $app['log.options'];
$logOptions['monolog.logfile'] = __DIR__.'/../resources/logs/queue.log';
$app->register(new MonologServiceProvider(), $logOptions);

//Set-up mail
$app->register(new SwiftmailerServiceProvider());
$app['swiftmailer.options'] = $app['mail.options'];

//Init serializer
$app->register(new SerializerProvider());

//Connect and configure MongoDb
$app->register(new MongoDBODMServiceProvider(), array(
    'doctrine.odm.mongodb.connection_options'      => $app['mongodb.options'],
    'doctrine.odm.mongodb.documents' => array(
        0 => array(
            'type' => 'annotation',
            'path' => array(
                __DIR__.'/../app/Models',
            ),
            'namespace' => 'Models'
        ),
    ),
    'doctrine.odm.mongodb.proxies_dir'             => __DIR__.'/../resources/cache/mongodb/Proxy',
    'doctrine.odm.mongodb.proxies_namespace'       => 'DoctrineMongoDBProxy',
    'doctrine.odm.mongodb.auto_generate_proxies'   => true,
    'doctrine.odm.mongodb.hydrators_dir'           => __DIR__.'/../resources/cache/mongodb/Hydrator',
    'doctrine.odm.mongodb.hydrators_namespace'     => 'DoctrineMongoDBHydrator',
    'doctrine.odm.mongodb.auto_generate_hydrators' => true,
    'doctrine.odm.mongodb.metadata_cache'          => new \Doctrine\Common\Cache\ArrayCache(),
    'doctrine.odm.mongodb.logger_callable'         => $app->protect(function($query) {
            // log your query
        })
));


/**
 * Start the ZeroMQ server
 */
$server = Server::factory('tcp://127.0.0.1:4444', $app);
$server->registerOnMessageCallback(function ($msg) use($app) {
    $app['monolog']->addInfo(sprintf("Message '%s' received.", $msg));

    try {
        $request = Request::factory($msg);

        $workerClass = '\\Zmqueue\\Worker\\' . ucfirst($request->getWorker());
        if (class_exists($workerClass) === false) {
            throw new ServerException('No class for given worker.');
        }

        $worker = new $workerClass($request, $app);
        if (!$worker instanceof Worker) {
            throw new ServerException('Class must be a valid worker.');
        }

        return $worker;
    } catch (\Exception $e) {
        $app['monolog']->addError(sprintf("Error handling request with message: '%s'.", $e->getMessage()));
        return new Response(Response::RESPONSE_ERROR, $e->getMessage());
    }
});

$server->run();