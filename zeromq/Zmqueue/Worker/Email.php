<?php

namespace Zmqueue\Worker;


use Zmqueue\Request;
use Zmqueue\ServerException;
use Zmqueue\Worker;
use Service\Queue\Email as MailService;

class Email implements Worker
{
    protected $request;
    protected $data;
    protected $app;

    public function __construct(Request $request, \Silex\Application $app)
    {
        if (!$this->isValid($request)) {
            throw new ServerException('Invalid data for worker given.');
        }
        $this->data = $request->getWorkerData();
        $this->request = $request;
        $this->app = $app;
    }

    public function isValid(Request $request)
    {
        $valid = false;

        $data = $request->getWorkerData();

        if (is_string($data[MailService::MAIL_RECIPIENT])
            && is_string($data[MailService::MAIL_TITLE])
            && is_string($data[MailService::MAIL_TEMPLATE])
            && is_array($data[MailService::MAIL_VARIABLES])) {
            $valid = true;
        }

        return $valid;
    }

    public function __invoke()
    {
        $mailContents = MailService::getMailContent($this->data[MailService::MAIL_TEMPLATE], $this->data[MailService::MAIL_VARIABLES]);

        //Send e-mail to user with invite for group
        $message = \Swift_Message::newInstance()
            ->setSubject($this->data[MailService::MAIL_TITLE])
            ->setFrom('socializr.io@gmail.com')
            ->setTo($this->data[MailService::MAIL_RECIPIENT])
            ->setBody($mailContents)
            ->setContentType("text/html");

        $this->app['monolog']->addInfo(sprintf(
            "Sending e-mail with Subject: '%s' to Recipient: '%s'",
            $this->data[MailService::MAIL_TITLE],
            $this->data[MailService::MAIL_RECIPIENT]
        ));


        $result = $this->app['mailer']->send($message);
        if ($this->app['mailer.initialized']) {
            $this->app['swiftmailer.spooltransport']->getSpool()->flushQueue($this->app['swiftmailer.transport']);
        }

        if (!$result) {
            throw new ServerException('Email can\'t be sent.');
        }
    }
} 