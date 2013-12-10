<?php

namespace Auth;

use Models\User;

class OauthUser extends User
{
    /**
     * Constructor
     *
     * @param $username
     * @param $password
     * @param $email
     * @param array $roles
     * @param bool $enabled
     * @param bool $userNonExpired
     * @param bool $credentialsNonExpired
     * @param bool $userNonLocked
     * @throws \InvalidArgumentException
     */
    public function __construct($username, $password, $email, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->userName = $username;
        $this->password = $password;
        $this->email = $email;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }
}