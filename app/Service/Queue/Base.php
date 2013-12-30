<?php

namespace Service\Queue;

use Zmqueue\Client;

class Base
{
    protected $queue;
    protected $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        $this->queue = Client::factory('tcp://127.0.0.1:4444');
    }
} 