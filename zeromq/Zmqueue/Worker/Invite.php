<?php

namespace Zmqueue\Worker;

use Zmqueue\Request;
use Zmqueue\ServerException;
use Zmqueue\Worker;
use Service\Queue\Email as MailService;
use Service\Queue\Invite as InviteService;

class Invite implements Worker
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

        var_dump($data);

        if (is_string($data[InviteService::INVITED_USER_ID])
            && is_string($data[MailService::MAIL_RECIPIENT])
            && is_string($data[MailService::MAIL_TITLE])
            && is_array($data[MailService::MAIL_VARIABLES])
            && is_array($data[InviteService::INVITE])
            && is_string($data[InviteService::INVITE]['group_id'])
            && is_string($data[InviteService::INVITE]['hash'])
        ) {
            $valid = true;
        }

        return $valid;
    }

    public function __invoke()
    {
        $userId = $this->data[InviteService::INVITED_USER_ID];
        $invitedUser = $this->app['doctrine.odm.mongodb.dm']
            ->createQueryBuilder('Models\\User')
            ->field('_id')->equals($userId)
            ->getQuery()
            ->getSingleResult();

        if (!$invitedUser) {
            throw new ServerException(sprintf('The invited user with id %s can\'t be found.', $userId));
        }

        $mailContents = MailService::getMailContent('invite', $this->data[MailService::MAIL_VARIABLES]);

        //Send e-mail to user with invite for group
        $message = \Swift_Message::newInstance()
            ->setSubject($this->data[MailService::MAIL_TITLE])
            ->setFrom('socializr.io@gmail.com')
            ->setTo($this->data[MailService::MAIL_RECIPIENT])
            ->setBody($mailContents)
            ->setContentType("text/html");

        //log the intention of sending an invite
        $this->app['monolog']->addInfo(sprintf(
            "Sending invite e-mail with Subject: '%s' to Recipient: '%s'",
            $this->data[MailService::MAIL_TITLE],
            $this->data[MailService::MAIL_RECIPIENT]
        ));

        //send mail and flush memory
        $result = $this->app['mailer']->send($message);
        if ($this->app['mailer.initialized']) {
            $this->app['swiftmailer.spooltransport']->getSpool()->flushQueue($this->app['swiftmailer.transport']);
        }

        //Save to the database when email is sent
        if ($result) {
            $invite = new \Models\Invite();
            $invite->setGroupId($this->data[InviteService::INVITE]['group_id']);
            $invite->setHash($this->data[InviteService::INVITE]['hash']);

            $invitedUser->addInvite($invite);

            $this->app['doctrine.odm.mongodb.dm']->persist($invitedUser);
            $this->app['doctrine.odm.mongodb.dm']->flush();
        } else {
            throw new ServerException('Email can\'t be sent.');
        }
    }
}