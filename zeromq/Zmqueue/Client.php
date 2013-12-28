<?php

namespace Zmqueue;

class Client
{
    const SOCKET_LINGER_MSECS = 0;//Timeout after * miliseconds
    const POLL_RESPONSE_TIMEOUT = 2500;//Msecs
    const RESPONSE_RETRIES = 3;

    private $socket;
    private $socketDealer;
    private $connected = false;
    private $output;

    public function __construct(\ZMQSocket $socketDealer)
    {
        $this->socketDealer = $socketDealer;
    }

    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    public function run(Request $request)
    {
        $request = $request->getJson();
        $this->connect();
        $this->socketDealer->send($request);
        return $this->getOutput($request);
    }

    public function connect()
    {
        $this->connected = false;
        $this->socketDealer->connect($this->socket);
        $this->connected = true;
    }

    protected function reconnect()
    {
        unset($this->socketDealer);
        $this->socketDealer = self::getDealer();
        $this->connect();
    }

    protected function getOutput($request)
    {
        $retries_left = self::RESPONSE_RETRIES;
        $read = $write = array();
        while ($retries_left) {
            //  Poll socket for a reply, with timeout
            $poll = new \ZMQPoll();
            $poll->add($this->socketDealer, \ZMQ::POLL_IN);
            $events = $poll->poll($read, $write, self::POLL_RESPONSE_TIMEOUT);

            //  If we got a reply, process it
            if ($events) {
                $response = $this->socketDealer->recv();
                return Response::factory($response);
            } elseif ($retries_left--) {
                $this->reconnect();
                $this->socketDealer->send($request);
            } else {
                throw new ClientException('Can\'t connect to queue server.');
                break;	//  Give up
            }
        }
    }

    static protected function getDealer()
    {
        $context = new \ZMQContext();
        $dealer  = $context->getSocket(\ZMQ::SOCKET_DEALER);
        $dealer->setSockOpt(\ZMQ::SOCKOPT_LINGER, self::SOCKET_LINGER_MSECS);
        return $dealer;
    }

    static public function factory($socket)
    {
        $dealer  = self::getDealer();
        $queue = new Client($dealer);
        $queue->setSocket($socket);

        return $queue;
    }
}