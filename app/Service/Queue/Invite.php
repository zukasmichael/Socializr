<?php

namespace Service\Queue;

use Zmqueue\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Invite extends Base
{
    const INVITE = 'invite';
    const INVITED_USER_ID = 'invitedUserId';

    /**
     * Queue an invite
     *
     * @param \Models\Invite $invite
     * @param \Models\Group $group
     * @param \Models\User $recipient
     * @param \Models\User $sender
     * @return mixed
     */
    public function queueInvite(\Models\Invite $invite, \Models\Group $group, \Models\User $recipient, \Models\User $sender)
    {
        $hash = sha1(openssl_random_pseudo_bytes(32));

        $invite->setHash($hash);

        $acceptUri = $this->app['url_generator']->generate('userAcceptInvite', array(
            'hash' => $hash
        ), UrlGenerator::ABSOLUTE_URL);
        $groupUri = $this->app['angular.urlGenerator']->generate('groupDetails', array(
            'id' => $group->getId()
        ), UrlGenerator::ABSOLUTE_URL);

        $mailTemplateVars = array(
            '%%SENDERUSERNAME%%' => $sender->getUserName(),
            '%%GROUPNAME%%' => $group->getName(),
            '%%GROUPURI%%' => $groupUri,
            '%%ACCEPTURI%%' => $acceptUri
        );

        $jsonData[self::INVITE] = $invite;
        $jsonData[self::INVITED_USER_ID] = $recipient->getId();
        $jsonData[Email::MAIL_RECIPIENT] = $recipient->getEmail();
        $jsonData[Email::MAIL_TITLE] = 'Socializr - U bent uitgenodigd voor een nieuwe groep!';
        $jsonData[Email::MAIL_VARIABLES] = $mailTemplateVars;

        $request = new Request('Invite', $jsonData);
        $request->setSerializer($this->app['serializer']);
        $result = $this->queue->run($request);

        return $result;
    }
}