<?php

namespace LoginProvider;

use Gigablah\Silex\OAuth\Event\GetUserForTokenEvent;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Gigablah\Silex\OAuth\Security\User\StubUser;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken;
use \Doctrine\ODM\MongoDB\DocumentManager;
use \LoginProvider\SessionUser;

/**
 * To intercept the UserProviderListener functionality provided by the plugin
 *
 * Class UserProviderListener
 * @package LoginProvider
 */
class UserProviderListener extends \Gigablah\Silex\OAuth\EventListener\UserProviderListener
{
    const SERVICE_FACEBOOK = 'facebook';
    const SERVICE_TWITTER = 'twitter';
    const SERVICE_GOOGLE = 'google';
    const SERVICE_GITHUB = 'github';

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager;
     */
    private $dm;

    /**
     * Constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Populate the security token with a user from the local database.
     *
     * @param GetUserForTokenEvent $event
     */
    public function onGetUser(GetUserForTokenEvent $event)
    {
        $userProvider = $event->getUserProvider();

        if (!$userProvider instanceof OAuthUserProviderInterface) {
            return;
        }

        $token = $event->getToken();

        if ($user = $userProvider->loadUserByOAuthCredentials($token)) {
            $sessionUser = $this->loadUser($user, $token);
            $token->setUser($sessionUser);
        }
    }

    /**
     * Load a user
     *
     * @param StubUser $user
     * @param OAuthToken $token
     * @return \LoginProvider\SessionUser
     */
    protected function loadUser(StubUser $user, OAuthToken $token)
    {
        $sessionUser = new SessionUser($user);

        //Find user by provider id
        $appUser = $this->dm->createQueryBuilder('Models\\User')
            ->field('loginProviderId.'.$token->getService())
            ->equals($token->getUid())
            ->getQuery()
            ->getSingleResult();
        if ($appUser) {
            $sessionUser->setUser($appUser);
            return $sessionUser;
        }

        //If user not found, find user by email
        $appUser = $this->dm->getRepository('Models\\User')->findOneBy(['email' => $user->getEmail()]);
        if ($appUser) {
            $appUser = $this->addProviderForUser($user, $token);
            $sessionUser->setUser($appUser);
            return $sessionUser;
        }

        //If user not found, register user
        $appUser = $this->registerUser($user, $token);
        $sessionUser->setUser($appUser);
        return $sessionUser;
    }

    /**
     * Add the current provider for the user
     *
     * @param \Models\User $appUser
     * @param OAuthToken $token
     * @return \Models\User
     */
    protected function addProviderForUser(\Models\User $appUser, OAuthToken $token)
    {
        $appUser->setProviderId($token->getService(), $token->getUid());
        $this->dm->persist($appUser);
        $this->dm->flush();
        return $appUser;
    }

    /**
     * Register a user
     *
     * @param StubUser $user
     * @param OAuthToken $token
     * @return \Models\User
     */
    protected function registerUser(StubUser $user, OAuthToken $token)
    {
        $appUser = new \Models\User();
        $appUser->loadFromOauthUser($user);
        $appUser->setProviderId($token->getService(), $token->getUid());

        $this->dm->persist($appUser);
        $this->dm->flush();

        return $appUser;
    }
}