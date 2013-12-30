<?php

namespace Zmqueue;

class Server
{
    private $socket;
    private $socketDealer;
    private $callback;
    private $app;

    public function __construct(\ZMQSocket $socketDealer)
    {
        $this->socketDealer = $socketDealer;
    }

    public static function factory($socket, \Silex\Application $app)
    {
        $context      = new \ZMQContext();
        $socketDealer = $context->getSocket(\ZMQ::SOCKET_DEALER);

        $queueServer = new Server($socketDealer);
        $queueServer->setSocket($socket);
        $queueServer->setApplication($app);

        return $queueServer;
    }

    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    public function setApplication(\Silex\Application $app)
    {
        $this->app = $app;
    }

    public function registerOnMessageCallback(Callable $callback)
    {
        $this->callback = $callback;
    }

    public function run()
    {
        $this->socketDealer->bind($this->socket);

        while (true) {
            $this->tick();
        }
    }

    public function tick()
    {
        $msg = $this->socketDealer->recv();
        $result = $this->invokeCallback($msg);
        $this->handleCallbackResult($result);
    }

    public function invokeCallback($msg)
    {
        return call_user_func($this->callback, $msg);
    }

    public function handleCallbackResult($result)
    {
        if ($result instanceof Response) {
            $result->setSerializer($this->app['serializer']);
            $result = $result->getJson();
        }

        if (!$result instanceof Worker) {
            $this->socketDealer->send($result);
            return true;
        }

        $worker = $result;
        $response = new Response(RESPONSE::RESPONSE_OK, 'Task queued');
        $response->setSerializer($this->app['serializer']);
        $result = $response->getJson();
        $this->socketDealer->send($result);

        //do Work
        try {
            $worker();
        } catch (\Exception $e) {
            $this->app['monolog']->addError(sprintf(
                "Error for worker: '%s' with message: '%s'.",
                get_class($worker),
                $e->getMessage()
            ));
        }
    }
}