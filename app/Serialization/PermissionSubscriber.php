<?php

namespace Serialization;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Silex\Application;

class PermissionSubscriber implements EventSubscriberInterface
{
    protected $app;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
        );
    }

    /**
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $type = $event->getType();
        $type = is_array($type) ? $type['name'] : $type;

        if ($type == 'Models\\Group') {
            $group = $this->preSerializeGroup($event->getObject());

            if ($group === null) {
                $event->setType('stdClass');
            }
        }
    }

    /**
     * Get the sanitized group, according to permissions
     * @param \Models\Group $group
     * @return \Models\Group|null
     */
    protected function preSerializeGroup(\Models\Group $group)
    {
        $user = $this->app['user'];
        if (!$user) {
            $user = $this->app['anonymous_user'];
        }

        //If we have no permission
        if (!$user->hasPermissionForGroup($group, \Models\Permission::READONLY)) {
            if ($group->getVisibility() == \Models\Group::VISIBILITY_SECRET) {
                $group = null;
            } else {
                $group->setBoards(array());
                $group->setDescription(null);
            }
        }
        return $group;
    }
}