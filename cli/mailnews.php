<?php

/**
 * Check the time, because the cron runs every hour
 * We want this script to only run once a day
 */
define('CRON_RUN_AT_HOUR', 18);

$now = new \DateTime();
if ((int)$now->format('HH') !== CRON_RUN_AT_HOUR) {
    die('Not the right time to run now...');
}

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

$app->register(new Service\AngularServiceProvider());

//Set-up logging
$logOptions = $app['log.options'];
$logOptions['monolog.logfile'] = __DIR__.'/../resources/logs/cron.log';
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

$profileUri = $this->app['angular.urlGenerator']->generate('userProfile', array(), UrlGenerator::ABSOLUTE_URL);

//Get all users
$users = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User')
    ->field('enabled')->equals(true)
    ->getQuery()
    ->execute();

foreach ($users as $user) {
    $userEmail = $user->getEmail();
    if (!$userEmail) {
        continue;
    }

    $profile = $app['doctrine.odm.mongodb.dm']->getRepository('Models\\Profile')->findByUser($user);
    if (!$profile) {
        continue;
    }

    $permissionGroupIds = $user->getPermissionGroupIds();
    if (empty($permissionGroupIds)) {
        continue;
    }

    $weekAgo = (new \DateTime())->modify('midnight')->modify('-1 week');
    $messages = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Message')
        ->field('groupId')->in($permissionGroupIds)
        ->field('createdAt')->lt($weekAgo)
        ->sort('createdAt', 'desc')
        ->limit(20)
        ->getQuery()
        ->execute()
        ->toArray();

    if (empty($messages)) {
        continue;
    }

    $groupIds = [];
    foreach ($messages as $message) {
        if (!in_array($message->getGroupId(), $groupIds)) {
            $groupIds[] = $message->getGroupId();
        }
    }

    $groups = $app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group')
        ->field('_id')->in($groupIds)
        ->getQuery()
        ->execute()
        ->toArray();

    $postContents = [];
    foreach ($messages as $message)
    {
        $group = $groups[$message->getGroupId()];
        $groupUri = '';
        $groupName = '';
        if ($group) {
            $groupUri = $this->app['angular.urlGenerator']->generate('groupDetails', array(
                'id' => $group->getId()
            ), UrlGenerator::ABSOLUTE_URL);
            $groupName = $group->getName();
        }

        $postUser = $message->getPostUser();
        $postUserName = '';
        if ($postUser) {
            $postUserName = $postUser->getUserName();
        }

        $createdAt = $message->getCreatedAt();
        $postDateTime = '';
        if ($createdAt) {
            $postDateTime = $createdAt->format('d-m-Y hh:ss');
        }

        $postContents[] = MailService::getMailContent('newspost', [
            '%%MESSAGETITLE%%' => (string)$message->getTitle(),
            '%%GROUPNAME%%' => $groupName,
            '%%MESSAGECONTENT%%' => (string)$message->getContents(),
            '%%POSTUSER%%' => $postUserName,
            '%%POSTDATETIME%%' => $postDateTime,
            '%%GROUPURI%%' => $groupUri
        ]);
    }

    $messagesContent = '';
    foreach ($postContents as $mailPost) {
        $messagesContent .= $mailPost;
    }

    $mailContent = MailService::getMailContent('news', [
        '%%PROFILEURI%%' => $profileUri,
        '%%MESSAGES%%' => $messagesContent
    ]);

    $mailTitle = 'Socializr update';
    //Send e-mail to user with invite for group
    $message = \Swift_Message::newInstance()
        ->setSubject($mailTitle)
        ->setFrom('socializr.io@gmail.com')
        ->setTo($userEmail)
        ->setBody($mailContent)
        ->setContentType("text/html");

    $this->app['monolog']->addInfo(sprintf(
        "Sending e-mail with Subject: '%s' to Recipient: '%s'",
        $mailTitle,
        $userEmail
    ));

    $result = $this->app['mailer']->send($message);
}

if ($this->app['mailer.initialized']) {
    $this->app['swiftmailer.spooltransport']->getSpool()->flushQueue($this->app['swiftmailer.transport']);
}