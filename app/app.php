<?php

use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Neutron\Silex\Provider\MongoDBODMServiceProvider;
use Macedigital\Silex\Provider\SerializerProvider;
use \LoginProvider\UserProviderListener;
use JDesrosiers\Silex\Provider\CorsServiceProvider;

$app->register(new HttpCacheServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider()); // for CSRF token

/**
 * MongoDb
 */
$app->register(new MonologServiceProvider(), $app['log.options']);
$app->register(new MongoDBODMServiceProvider(), array(
    'doctrine.odm.mongodb.connection_options'      => $app['mongodb.options'],
    'doctrine.odm.mongodb.documents' => array(
        0 => array(
            'type' => 'annotation',
            'path' => array(
                __DIR__.'/Models',
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
$app->register(new SerializerProvider());

/**
 * Oauth
 */
$app->register(new Gigablah\Silex\OAuth\OAuthServiceProvider(), array(
    'oauth.services' => array(
        UserProviderListener::SERVICE_FACEBOOK => array(
            'key' => $app['login.providers'][UserProviderListener::SERVICE_FACEBOOK]['API_KEY'],
            'secret' => $app['login.providers']['facebook']['API_SECRET'],
            'scope' => array('email'),
            'user_endpoint' => 'https://graph.facebook.com/me'
        ),
        UserProviderListener::SERVICE_TWITTER => array(
            'key' => $app['login.providers'][UserProviderListener::SERVICE_TWITTER]['API_KEY'],
            'secret' => $app['login.providers']['twitter']['API_SECRET'],
            'scope' => array(),
            'user_endpoint' => 'https://api.twitter.com/1.1/account/verify_credentials.json'
        )/*,
        UserProviderListener::SERVICE_GOOGLE => array(
            'key' => GOOGLE_API_KEY,
            'secret' => GOOGLE_API_SECRET,
            'scope' => array(
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ),
            'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
        ),
        UserProviderListener::SERVICE_GITHUB => array(
            'key' => GITHUB_API_KEY,
            'secret' => GITHUB_API_SECRET,
            'scope' => array('user:email'),
            'user_endpoint' => 'https://api.github.com/user'
        )*/
    )
));

$securityOptions = array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                'login_path' => '/auth/{service}',
                'callback_path' => '/auth/{service}/callback',
                'check_path' => '/auth/{service}/check',
                'failure_path' => '/loginfailed',
                'with_csrf' => true
            ),
            'logout' => array(
                'logout_path' => '/logout',
                'with_csrf' => true
            ),
            'users' => new Gigablah\Silex\OAuth\Security\User\Provider\OAuthInMemoryUserProvider()
        )
    ),
    'security.access_rules' => array(
        array('^/auth', 'ROLE_USER'),
        //array('^/(?!login).+', 'ROLE_USER')//Disable this line to allow access for all users
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ANONYMOUS'),
        'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN', 'ROLE_USER', 'ROLE_ANONYMOUS')
    )
);
$app->register(new Silex\Provider\SecurityServiceProvider(), $securityOptions);


$app['oauth.user_info_listener'] = $app->share(function ($app) {
    return new \LoginProvider\UserInfoListener($app['oauth'], $app['oauth.services']);
});
$app['oauth.user_provider_listener'] = $app->share(function ($app) {
    return new \LoginProvider\UserProviderListener($app['doctrine.odm.mongodb.dm']);
});

$app->register(new CorsServiceProvider(), array(
    "cors.allowOrigin" => "https://socializr.dev",
));

$app->after($app["cors"]);