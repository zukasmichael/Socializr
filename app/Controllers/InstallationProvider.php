<?php

namespace Controllers;

use Models\Permission;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppException\AccessDenied;
use AppException\ResourceNotFound;

/**
 * Handles all /message routes
 *
 * Class MessageProvider
 * @package Controllers
 */
class InstallationProvider extends AbstractProvider
{
    /**
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = parent::connect($app);

        /**
         * Get message by id
         */
        $controllers->get('/doinstall', function (Request $request) use ($app) {

            $user = $app['user'];
            if (!$user) {
                throw new AccessDenied('You have to be logged-in for installation.');
            }

            $this->cleanInstall($user);

            $responseHtml = '<h1>Installation DONE</h1>' .
                '<a href="/install">Return to installation menu.</a>';
            return $responseHtml;
        });

        /**
         * Get message by id
         */
        $controllers->get('/dopopulate', function (Request $request) use ($app) {
            $user = $app['user'];
            if (!$user || !$user->isSuperAdmin()) {
                throw new AccessDenied('You have to be logged-in as SUPER ADMIN for db population, perform installation to install yourself as super user.');
            }

            $this->populate($user);

            $responseHtml = '<h1>Population DONE</h1>' .
                '<a href="/install">Return to installation menu.</a>';
            return $responseHtml;
        });

        return $controllers;
    }

    /**
     * @param \Models\User $user
     */
    protected function cleanInstall(\Models\User $user)
    {
        //Clear all users
        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Message');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Pinboard');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Profile');
        $qb->remove()
            ->getQuery()
            ->execute();
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user->setRoles(array(\Models\User::ROLE_USER, \Models\User::ROLE_SUPER_ADMIN));
        $user->setInvites(array());
        $user->setPermissions(array());
        $user->enable();

        $profile = new \Models\Profile();
        $profile->setInterests(array());
        $profile->setAbout('');
        $profile->setBirthday('1855-01-16T23:20:30.000Z');
        $this->app['doctrine.odm.mongodb.dm']->persist($profile);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user->setProfileId($profile->getId());

        //The user object is serialized from the session and needs do be merged with the documentManager for saving
        $user = $this->app['doctrine.odm.mongodb.dm']->merge($user);

        $this->app['doctrine.odm.mongodb.dm']->persist($user);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $this->app['service.updateSessionUser']($user);
        return $user;
    }

    /**
     * @param \Models\User $user
     * @param string $secondEmail
     * @throws \AppException\AccessDenied
     */
    public function populate(\Models\User $user, $secondEmail = 'srooijde@twitter.com')
    {
        if (!$user || !$user->isSuperAdmin()) {
            throw new AccessDenied('You have to be logged-in as SUPER ADMIN for db population, perform installation to install yourself as super user.');
        }

        $user = $this->cleanInstall($user);

        $profile2 = new \Models\Profile();
        $profile2->setInterests(array());
        $profile2->setAbout('');
        $profile2->setBirthday('1855-01-16T23:20:30.000Z');
        $profile3 = new \Models\Profile();
        $profile3->setInterests(array());
        $profile3->setAbout('');
        $profile3->setBirthday('1855-01-16T23:20:30.000Z');
        $profile4 = new \Models\Profile();
        $profile4->setInterests(array());
        $profile4->setAbout('');
        $profile4->setBirthday('1855-01-16T23:20:30.000Z');

        $this->app['doctrine.odm.mongodb.dm']->persist($profile2);
        $this->app['doctrine.odm.mongodb.dm']->persist($profile3);
        $this->app['doctrine.odm.mongodb.dm']->persist($profile4);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user = $this->app['doctrine.odm.mongodb.dm']->merge($user);

        $userGroups = $this->createGroupBoardMessage($user);

        $user2 = new \Models\User();
        $user2->setEmail('newguy@example.com');
        $user2->addRole(\Models\User::ROLE_USER);
        $user2->setUserName('NewGuy Example');
        $user2->setProfileId($profile2->getId());
        $user2->enable();
        $this->app['doctrine.odm.mongodb.dm']->persist($user2);

        $user3 = new \Models\User();
        $user3->setEmail($secondEmail);
        $user3->addRole(\Models\User::ROLE_USER);
        $user3->setUserName('Srooijde Twitter Example');
        $user3->setProfileId($profile3->getId());
        $user3->enable();
        $this->app['doctrine.odm.mongodb.dm']->persist($user3);

        $user4 = new \Models\User();
        $user4->setEmail('otheruser@example.com');
        $user4->addRole(\Models\User::ROLE_USER);
        $user4->setUserName('Other User Example');
        $user4->setProfileId($profile4->getId());
        $user4->enable();
        $this->app['doctrine.odm.mongodb.dm']->persist($user4);

        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user3Groups = $this->createGroupBoardMessage($user3);
        $user4Groups = $this->createGroupBoardMessage($user4);

        /**
         * Make some users admin or member for other groups
         *
         * Build this permission scheme:
         *
         * [Super Admin] => [
         *     [$secondEmail] => [
         *         ['group1'] => MEMBER,
         *         ['group3'] => MEMBER,
         *         ['group4'] => ADMIN,
         *     ],
         *     ['otheruser@example.com'] => [
         *         ['group2'] => MEMBER,
         *         ['group3'] => BLOCKED,
         *     ]
         * ],
         *
         * [$secondEmail] => [
         *     ['Super Admin'] => [
         *         ['group1'] => BLOCKED,
         *         ['group2'] => MEMBER,
         *         ['group5'] => MEMBER,
         *     ],
         *     ['otheruser@example.com'] => [
         *         ['group1'] => MEMBER,
         *         ['group3'] => ADMIN,
         *         ['group4'] => MEMBER,
         *     ]
         * ]
         *
         * [otheruser@example.com] => [
         *     ['Super Admin'] => [
         *         ['group2'] => MEMBER,
         *         ['group3'] => MEMBER,
         *         ['group4'] => BLOCKED,
         *     ],
         *     [$secondEmail] => [
         *         ['group1'] => ADMIN,
         *         ['group3'] => ADMIN,
         *         ['group4'] => MEMBER,
         *     ]
         * ]
         *
         * [newguy@example.com] => []
         */

        //Super Admin permissions
        $user->setPermissionForGroup($user3Groups[0]->getId(), \Models\Permission::MEMBER);
        $user->setPermissionForGroup($user3Groups[2]->getId(), \Models\Permission::MEMBER);
        $user->setPermissionForGroup($user3Groups[3]->getId(), \Models\Permission::ADMIN);

        $user->setPermissionForGroup($user4Groups[1]->getId(), \Models\Permission::MEMBER);
        $user->setPermissionForGroup($user4Groups[2]->getId(), \Models\Permission::BLOCKED);

        //update the session storage if we are updating the session user
        $this->app['service.updateSessionUser']($user);
        $this->app['doctrine.odm.mongodb.dm']->persist($user);

        //srooijde twitter permissions
        $user3->setPermissionForGroup($userGroups[0]->getId(), \Models\Permission::BLOCKED);
        $user3->setPermissionForGroup($userGroups[1]->getId(), \Models\Permission::MEMBER);
        $user3->setPermissionForGroup($userGroups[4]->getId(), \Models\Permission::MEMBER);

        $user3->setPermissionForGroup($user4Groups[0]->getId(), \Models\Permission::MEMBER);
        $user3->setPermissionForGroup($user4Groups[2]->getId(), \Models\Permission::ADMIN);
        $user3->setPermissionForGroup($user4Groups[3]->getId(), \Models\Permission::MEMBER);
        $this->app['doctrine.odm.mongodb.dm']->persist($user3);

        //otheruser@example.com permissions
        $user4->setPermissionForGroup($userGroups[1]->getId(), \Models\Permission::MEMBER);
        $user4->setPermissionForGroup($userGroups[2]->getId(), \Models\Permission::MEMBER);
        $user4->setPermissionForGroup($userGroups[3]->getId(), \Models\Permission::BLOCKED);

        $user4->setPermissionForGroup($user3Groups[0]->getId(), \Models\Permission::ADMIN);
        $user4->setPermissionForGroup($user3Groups[2]->getId(), \Models\Permission::ADMIN);
        $user4->setPermissionForGroup($user3Groups[3]->getId(), \Models\Permission::MEMBER);
        $this->app['doctrine.odm.mongodb.dm']->persist($user4);

        $this->app['doctrine.odm.mongodb.dm']->flush();
    }

    public function createGroupBoardMessage(\Models\User $user)
    {
        /**
         * Create groups, boards, messages for the SUPER user
         */
        $group1 = new \Models\Group;
        $group1->setName('Example 1');
        $group1->setDescription('First group, open, by ' . $user->getUserName());
        $group1->setVisibility(\Models\Group::VISIBILITY_OPEN);

        $group2 = new \Models\Group;
        $group2->setName('Example 2');
        $group2->setDescription('Second group, open, by ' . $user->getUserName());
        $group2->setVisibility(\Models\Group::VISIBILITY_OPEN);

        $group3 = new \Models\Group;
        $group3->setName('Example 3');
        $group3->setDescription('Third group, protected, by ' . $user->getUserName());
        $group3->setVisibility(\Models\Group::VISIBILITY_PROTECTED);

        $group4 = new \Models\Group;
        $group4->setName('Example 4');
        $group4->setDescription('Fourth group, secret, by ' . $user->getUserName());
        $group4->setVisibility(\Models\Group::VISIBILITY_SECRET);

        $group5 = new \Models\Group;
        $group5->setName('Example 5');
        $group5->setDescription('Fifth group, secret, by ' . $user->getUserName());
        $group5->setVisibility(\Models\Group::VISIBILITY_SECRET);

        $group6 = new \Models\Group;
        $group6->setName('Example 6');
        $group6->setDescription('Sixth group, open, by ' . $user->getUserName());
        $group6->setVisibility(\Models\Group::VISIBILITY_OPEN);

        $group7 = new \Models\Group;
        $group7->setName('Example 7');
        $group7->setDescription('Seventh group, open, by ' . $user->getUserName());
        $group7->setVisibility(\Models\Group::VISIBILITY_PROTECTED);

        $this->app['doctrine.odm.mongodb.dm']->persist($group1);
        $this->app['doctrine.odm.mongodb.dm']->persist($group2);
        $this->app['doctrine.odm.mongodb.dm']->persist($group3);
        $this->app['doctrine.odm.mongodb.dm']->persist($group4);
        $this->app['doctrine.odm.mongodb.dm']->persist($group5);
        $this->app['doctrine.odm.mongodb.dm']->persist($group6);
        $this->app['doctrine.odm.mongodb.dm']->persist($group7);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user->setPermissionForGroup($group1->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group2->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group3->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group4->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group5->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group6->getId(), \Models\Permission::ADMIN);
        $user->setPermissionForGroup($group7->getId(), \Models\Permission::ADMIN);

        $this->app['doctrine.odm.mongodb.dm']->persist($user);

        $board1 = new \Models\Pinboard();
        $board1->setTitle('Example board 1');
        $board1->setGroupId($group1->getId());
        $board1->setVisibility($group1->getVisibility());

        $board2 = new \Models\Pinboard();
        $board2->setTitle('Example board 2');
        $board2->setGroupId($group1->getId());
        $board2->setVisibility($group1->getVisibility());

        $board3 = new \Models\Pinboard();
        $board3->setTitle('Example board 3');
        $board3->setGroupId($group1->getId());
        $board3->setVisibility($group1->getVisibility());

        $board4 = new \Models\Pinboard();
        $board4->setTitle('Example board 4');
        $board4->setGroupId($group2->getId());
        $board4->setVisibility($group2->getVisibility());

        $board5 = new \Models\Pinboard();
        $board5->setTitle('Example board 5');
        $board5->setGroupId($group2->getId());
        $board5->setVisibility($group2->getVisibility());

        $board6 = new \Models\Pinboard();
        $board6->setTitle('Example board 6');
        $board6->setGroupId($group3->getId());
        $board6->setVisibility($group3->getVisibility());

        $board7 = new \Models\Pinboard();
        $board7->setTitle('Example board 7');
        $board7->setGroupId($group3->getId());
        $board7->setVisibility($group3->getVisibility());

        $board8 = new \Models\Pinboard();
        $board8->setTitle('Example board 8');
        $board8->setGroupId($group4->getId());
        $board8->setVisibility($group4->getVisibility());

        $this->app['doctrine.odm.mongodb.dm']->persist($board1);
        $this->app['doctrine.odm.mongodb.dm']->persist($board2);
        $this->app['doctrine.odm.mongodb.dm']->persist($board3);
        $this->app['doctrine.odm.mongodb.dm']->persist($board4);
        $this->app['doctrine.odm.mongodb.dm']->persist($board5);
        $this->app['doctrine.odm.mongodb.dm']->persist($board6);
        $this->app['doctrine.odm.mongodb.dm']->persist($board7);
        $this->app['doctrine.odm.mongodb.dm']->persist($board8);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $message1 = new \Models\Message();
        $message1->setTitle('Example1');
        $message1->setContents('Example message 1');
        $message1->setPostUser($user);
        $message1->setBoardId($board1->getId());
        $message1->setGroupId($board1->getGroupId());
        $message1->setVisibility($board1->getVisibility());
        $message1->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message1);

        $message2 = new \Models\Message();
        $message2->setTitle('Example2');
        $message2->setContents('Example message 2');
        $message2->setPostUser($user);
        $message2->setBoardId($board1->getId());
        $message2->setGroupId($board1->getGroupId());
        $message2->setVisibility($board1->getVisibility());
        $message2->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message2);

        $message3 = new \Models\Message();
        $message3->setTitle('Example3');
        $message3->setContents('Example message 3');
        $message3->setPostUser($user);
        $message3->setBoardId($board1->getId());
        $message3->setGroupId($board1->getGroupId());
        $message3->setVisibility($board1->getVisibility());
        $message3->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message3);

        $message4 = new \Models\Message();
        $message4->setTitle('Example4');
        $message4->setContents('Example message 4');
        $message4->setPostUser($user);
        $message4->setBoardId($board1->getId());
        $message4->setGroupId($board1->getGroupId());
        $message4->setVisibility($board1->getVisibility());
        $message4->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message4);

        $message5 = new \Models\Message();
        $message5->setTitle('Example5');
        $message5->setContents('Example message 5');
        $message5->setPostUser($user);
        $message5->setBoardId($board1->getId());
        $message5->setGroupId($board1->getGroupId());
        $message5->setVisibility($board1->getVisibility());
        $message5->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message5);

        $message6 = new \Models\Message();
        $message6->setTitle('Example6');
        $message6->setContents('Example message 6');
        $message6->setPostUser($user);
        $message6->setBoardId($board1->getId());
        $message6->setGroupId($board1->getGroupId());
        $message6->setVisibility($board1->getVisibility());
        $message6->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message6);

        $message7 = new \Models\Message();
        $message7->setTitle('Example7');
        $message7->setContents('Example message 7');
        $message7->setPostUser($user);
        $message7->setBoardId($board1->getId());
        $message7->setGroupId($board1->getGroupId());
        $message7->setVisibility($board1->getVisibility());
        $message7->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message7);

        $message8 = new \Models\Message();
        $message8->setTitle('Example8');
        $message8->setContents('Example message 8');
        $message8->setPostUser($user);
        $message8->setBoardId($board1->getId());
        $message8->setGroupId($board1->getGroupId());
        $message8->setVisibility($board1->getVisibility());
        $message8->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message8);

        $message9 = new \Models\Message();
        $message9->setTitle('Example9');
        $message9->setContents('Example message 9');
        $message9->setPostUser($user);
        $message9->setBoardId($board1->getId());
        $message9->setGroupId($board1->getGroupId());
        $message9->setVisibility($board1->getVisibility());
        $message9->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message9);

        $message10 = new \Models\Message();
        $message10->setTitle('Example10');
        $message10->setContents('Example message 10');
        $message10->setPostUser($user);
        $message10->setBoardId($board1->getId());
        $message10->setGroupId($board1->getGroupId());
        $message10->setVisibility($board1->getVisibility());
        $message10->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message10);

        $message11 = new \Models\Message();
        $message11->setTitle('Example11');
        $message11->setContents('Example message 11');
        $message11->setPostUser($user);
        $message11->setBoardId($board1->getId());
        $message11->setGroupId($board1->getGroupId());
        $message11->setVisibility($board1->getVisibility());
        $message11->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message11);

        $message12 = new \Models\Message();
        $message12->setTitle('Example12');
        $message12->setContents('Example message 12');
        $message12->setPostUser($user);
        $message12->setBoardId($board2->getId());
        $message12->setGroupId($board2->getGroupId());
        $message12->setVisibility($board2->getVisibility());
        $message12->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message12);

        $message13 = new \Models\Message();
        $message13->setTitle('Example13');
        $message13->setContents('Example message 13');
        $message13->setPostUser($user);
        $message13->setBoardId($board2->getId());
        $message13->setGroupId($board2->getGroupId());
        $message13->setVisibility($board2->getVisibility());
        $message13->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message13);

        $message14 = new \Models\Message();
        $message14->setTitle('Example14');
        $message14->setContents('Example message 14');
        $message14->setPostUser($user);
        $message14->setBoardId($board2->getId());
        $message14->setGroupId($board2->getGroupId());
        $message14->setVisibility($board2->getVisibility());
        $message14->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message14);

        $message15 = new \Models\Message();
        $message15->setTitle('Example15');
        $message15->setContents('Example message 15');
        $message15->setPostUser($user);
        $message15->setBoardId($board3->getId());
        $message15->setGroupId($board3->getGroupId());
        $message15->setVisibility($board3->getVisibility());
        $message15->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message15);

        $message16 = new \Models\Message();
        $message16->setTitle('Example16');
        $message16->setContents('Example message 16');
        $message16->setPostUser($user);
        $message16->setBoardId($board3->getId());
        $message16->setGroupId($board3->getGroupId());
        $message16->setVisibility($board3->getVisibility());
        $message16->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message16);

        $message17 = new \Models\Message();
        $message17->setTitle('Example17');
        $message17->setContents('Example message 17');
        $message17->setPostUser($user);
        $message17->setBoardId($board4->getId());
        $message17->setGroupId($board4->getGroupId());
        $message17->setVisibility($board4->getVisibility());
        $message17->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message17);

        $message18 = new \Models\Message();
        $message18->setTitle('Example18');
        $message18->setContents('Example message 18');
        $message18->setPostUser($user);
        $message18->setBoardId($board6->getId());
        $message18->setGroupId($board6->getGroupId());
        $message18->setVisibility($board6->getVisibility());
        $message18->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message18);

        $message19 = new \Models\Message();
        $message19->setTitle('Example19');
        $message19->setContents('Example message 19');
        $message19->setPostUser($user);
        $message19->setBoardId($board7->getId());
        $message19->setGroupId($board7->getGroupId());
        $message19->setVisibility($board7->getVisibility());
        $message19->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message19);

        $message20 = new \Models\Message();
        $message20->setTitle('Example20');
        $message20->setContents('Example message 20');
        $message20->setPostUser($user);
        $message20->setBoardId($board7->getId());
        $message20->setGroupId($board7->getGroupId());
        $message20->setVisibility($board7->getVisibility());
        $message20->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message20);

        $message21 = new \Models\Message();
        $message21->setTitle('Example21');
        $message21->setContents('Example message 21');
        $message21->setPostUser($user);
        $message21->setBoardId($board7->getId());
        $message21->setGroupId($board7->getGroupId());
        $message21->setVisibility($board7->getVisibility());
        $message21->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message21);

        $message22 = new \Models\Message();
        $message22->setTitle('Example22');
        $message22->setContents('Example message 22');
        $message22->setPostUser($user);
        $message22->setBoardId($board7->getId());
        $message22->setGroupId($board7->getGroupId());
        $message22->setVisibility($board7->getVisibility());
        $message22->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message22);

        $message23 = new \Models\Message();
        $message23->setTitle('Example23');
        $message23->setContents('Example message 23');
        $message23->setPostUser($user);
        $message23->setBoardId($board8->getId());
        $message23->setGroupId($board8->getGroupId());
        $message23->setVisibility($board8->getVisibility());
        $message23->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message23);

        $message24 = new \Models\Message();
        $message24->setTitle('Example24');
        $message24->setContents('Example message 24');
        $message24->setPostUser($user);
        $message24->setBoardId($board8->getId());
        $message24->setGroupId($board8->getGroupId());
        $message24->setVisibility($board8->getVisibility());
        $message24->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message24);

        $message25 = new \Models\Message();
        $message25->setTitle('Example25');
        $message25->setContents('Example message 25');
        $message25->setPostUser($user);
        $message25->setBoardId($board8->getId());
        $message25->setGroupId($board8->getGroupId());
        $message25->setVisibility($board8->getVisibility());
        $message25->setCreatedAt(new \DateTime());

        $this->app['doctrine.odm.mongodb.dm']->persist($message25);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        return [$group1, $group2, $group3, $group4, $group5, $group6, $group7];
    }

    /**
     * Populate the db with no user
     */
    public function populateWithNoUser()
    {
        //Clear all users
        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\User');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Group');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Message');
        $qb->remove()
            ->getQuery()
            ->execute();

        $qb = $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('Models\\Pinboard');
        $qb->remove()
            ->getQuery()
            ->execute();
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $user = new \Models\User();
        $user->setEmail('otheruser@example.com');
        $user->addRole(\Models\User::ROLE_USER);
        $user->setUserName('Other User Example');

        $this->app['doctrine.odm.mongodb.dm']->persist($user);
        $this->app['doctrine.odm.mongodb.dm']->flush();

        $userGroups = $this->createGroupBoardMessage($user);
    }

    public function populateWithAdmin($secondEmail = 'srooijde@twitter.com')
    {
        $user = $this->app['user'];
        if (!$user) {
            throw new AccessDenied('You have to be logged-in for installation.');
        }

        $this->populate($user, $secondEmail);
    }
}