<?php
// disable the debug mode
$app['debug'] = false;

// Local
$app['locale'] = 'nl';
$app['session.default_locale'] = $app['locale'];

// Http cache
$app['http_cache.cache_dir'] = __DIR__ . '/../cache/';
$app['http_cache.esi'] = null;

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => 'silex_kitchen',
    'user'     => 'root',
    'password' => '',
);