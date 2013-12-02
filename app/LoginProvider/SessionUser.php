<?php

namespace LoginProvider;

use Gigablah\Silex\OAuth\Security\User\StubUser;

class SessionUser extends StubUser
{
    /**
     * @var \Gigablah\Silex\OAuth\Security\User\StubUser
     */
    protected $oauthUser;

    /**
     * @var \Models\User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param StubUser $oauthUser
     */
    public function __construct(StubUser $oauthUser)
    {
        $this->oauthUser = $oauthUser;
    }

    /**
     * @return \Models\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Models\User $user
     * @return $this
     */
    public function setUser(\Models\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the oauth user
     *
     * @return StubUser
     */
    public function getOauthUser()
    {
        return $this->oauthUser;
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->oauthUser->getEmail();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->oauthUser->getUsername();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->oauthUser->isAccountNonExpired();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->oauthUser->isAccountNonLocked();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->oauthUser->isCredentialsNonExpired();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->oauthUser->isEnabled();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->oauthUser->getRoles();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->oauthUser->getPassword();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->oauthUser->getSalt();
    }

    /**
     * Proxy method
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->oauthUser->eraseCredentials();
    }
} 