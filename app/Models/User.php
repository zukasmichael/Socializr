<?php

namespace Models;

use Auth\Listener\UserProviderListener;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\RuntimeException;

/**
 * @ODM\Document(
 *     collection="users",
 *     indexes={
 *         @ODM\Index(keys={"userName"="desc"}, options={"unique"=false}),
 *         @ODM\Index(keys={"email"="desc"}, options={"unique"=true})
 *     }
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class User implements AdvancedUserInterface
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getUserName",setter="setUserName")
     * @JMS\Type("string")
     */
    protected $userName;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getEmail",setter="setEmail")
     * @JMS\Type("string")
     */
    protected $email;

    /**
     * @ODM\Collection
     * @var array
     * @JMS\Accessor(getter="getRoles",setter="setRoles")
     * @JMS\Type("array<string>")
     */
    protected $roles = array();

    /**
     * @ODM\Hash
     * @var array
     * @JMS\Type("array<string, string>")
     * @JMS\Exclude
     */
    protected $loginProviderId = array(
        UserProviderListener::SERVICE_FACEBOOK => null,
        UserProviderListener::SERVICE_TWITTER => null,
        UserProviderListener::SERVICE_GOOGLE => null,
        UserProviderListener::SERVICE_GITHUB => null
    );

    /**
     * @var boolean
     * @ODM\Boolean
     * @JMS\Type("boolean")
     */
    protected $enabled = true;

    /**
     * @var boolean
     * @ODM\NotSaved
     * @JMS\Exclude
     */
    protected $password;

    /**
     * @var boolean
     * @ODM\NotSaved
     * @JMS\Exclude
     */
    protected $accountNonExpired = true;

    /**
     * @var boolean
     * @ODM\NotSaved
     * @JMS\Exclude
     */
    protected $credentialsNonExpired = true;

    /**
     * @var boolean
     * @ODM\NotSaved
     * @JMS\Exclude
     */
    protected $accountNonLocked = true;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return \Models\User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param $userName
     * @return \Models\User
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return \Models\User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the roles
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param $roles
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @param $role
     * @return $this
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string|null $service
     * @return string
     * @throws \RuntimeException
     */
    public function getProviderId($service = null)
    {
        if ($service === null) {
            return $this->loginProviderId;
        }
        if (array_key_exists($service, $this->loginProviderId) === false) {
            throw new RuntimeException("No login provider service $service configured.");
        }
        return $this->loginProviderId[$service];
    }

    /**
     * @param string $service
     * @param string $id
     * @throws \RuntimeException
     * @return $this
     */
    public function setProviderId($service, $id)
    {
        if (array_key_exists($service, $this->loginProviderId) === false) {
            throw new RuntimeException("No login provider service $service configured.");
        }
        $this->loginProviderId[$service] = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}