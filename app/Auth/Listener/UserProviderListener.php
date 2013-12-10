<?php

namespace Auth\Listener;

use Gigablah\Silex\OAuth\Event\GetUserForTokenEvent;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken;
use \Doctrine\ODM\MongoDB\DocumentManager;
use Models\User;
use Auth\OauthUser;

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

        if ($oauthUser = $userProvider->loadUserByOAuthCredentials($token)) {
            $user = $this->loadUser($oauthUser, $token);
            $token->setUser($user);
        }
    }

    /**
     * Load a user
     *
     * @param OauthUser $oauthUser
     * @param OAuthToken $token
     * @return User|object
     */
    protected function loadUser(OauthUser $oauthUser, OAuthToken $token)
    {
        //Find user by provider id
        $appUser = $this->dm->createQueryBuilder('Models\\User')
            ->field('loginProviderId.'.$token->getService())
            ->equals($token->getUid())
            ->getQuery()
            ->getSingleResult();
        if ($appUser) {
            return $appUser;
        }

        //If user not found, find user by email
        $appUser = $this->dm->getRepository('Models\\User')->findOneBy(['email' => $oauthUser->getEmail()]);
        if ($appUser) {
            $appUser = $this->addProviderForUser($appUser, $token);
            return $appUser;
        }

        //If user not found, register user
        return $this->registerUser($oauthUser, $token);
    }

    /**
     * Add the current provider for the user
     *
     * @param \Models\User $appUser
     * @param OAuthToken $token
     * @return \Models\User
     */
    protected function addProviderForUser(User $appUser, OAuthToken $token)
    {
        $appUser->setProviderId($token->getService(), $token->getUid());
        $this->dm->persist($appUser);
        $this->dm->flush();
        return $appUser;
    }

    /**
     * Register a user
     *
     * @param OauthUser $oauthUser
     * @param OAuthToken $token
     * @return User
     */
    protected function registerUser(OauthUser $oauthUser, OAuthToken $token)
    {
        $appUser = new User();
        $appUser->setProviderId($token->getService(), $token->getUid())
            ->setUserName($oauthUser->getUsername())
            ->setPassword($oauthUser->getPassword())
            ->setEmail($oauthUser->getEmail())
            ->setRoles($oauthUser->getRoles());

        $this->dm->persist($appUser);
        $this->dm->flush();

        return $appUser;
    }
}