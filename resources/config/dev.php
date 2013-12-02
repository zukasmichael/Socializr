<?php

// include the prod configuration
require __DIR__.'/prod.php';

// include the login providers configuration
// this file is out of versioning because of sensitive information
//require __DIR__.'/login_providers.php';

// enable the debug mode
$app['debug'] = true;