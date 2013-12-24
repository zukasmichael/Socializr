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
 *     },
 *     repositoryClass="\Models\UserRepository"
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
     * @JMS\Readonly
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details", "user-list", "user-details"})
     */
    protected $id;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getUserName",setter="setUserName")
     * @JMS\Type("string")
     * @JMS\Groups({"board-list", "board-details", "message-list", "message-details", "user-list", "user-details"})
     */
    protected $userName;

    /**
     * @ODM\String
     * @JMS\Accessor(getter="getEmail",setter="setEmail")
     * @JMS\Type("string")
     * @JMS\Groups({"user-list", "user-details"})
     */
    protected $email;

    /**
     * @ODM\Collection
     * @var array
     * @JMS\Accessor(getter="getRoles",setter="setRoles")
     * @JMS\Type("array<string>")
     * @JMS\Readonly
     * @JMS\Exclude
     */
    protected $roles = array();

    /**
     * @ODM\EmbedMany(
     *     targetDocument="\Models\Permission"
     * )
     * @JMS\Accessor(getter="getPermissions",setter="setPermissions")
     * @JMS\Type("array")
     * @JMS\Exclude
     */
    private $permissions = array();

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
     * @var string
     * @ODM\NotSaved
     * @JMS\Type("string")
     * @JMS\Groups({"user-details"})
     */
    protected $logoutUrl;

    /**
     * @var boolean
     * @ODM\Boolean
     * @JMS\Type("boolean")
     * @JMS\Groups({"user-list", "user-details"})
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
     * Get the permissions
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     * @return \Models\User
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @param Permission $permission
     * @return \Models\User
     */
    public function addPermission(\Models\Permission $permission)
    {
        $this->permissions[] = $permission;
        return $this;
    }

    /**
     * Get a permission for a group
     * @param string $groupId
     * @return \Models\Permission
     */
    public function getPermissionForGroup($groupId)
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getGroupId() == $groupId) {
                return $permission;
            }
        }
        $permission = new \Models\Permission();
        $permission->setGroupId($groupId);
        $this->permissions[] = $permission;
        return $permission;
    }

    /**
     * Set a permission for a group
     * @param string $groupId
     * @param int $accessLevel
     * @return \Models\User
     */
    public function setPermissionForGroup($groupId, $accessLevel)
    {
        if (empty($groupId)) {
            throw new \Exception('Can\'t set a permission for a non existing group.');
        }
        $this->getPermissionForGroup($groupId)->setAccessLevel($accessLevel);
        return $this;
    }

    /**
     * Check if user has a permission for a group
     * If the visibility for a group is protected or secret, we always check for accessLevel member or higher
     *
     * @param \Models\Group $group
     * @param int $accessLevel
     * @return bool
     */
    public function hasPermissionForGroup(\Models\Group $group, $accessLevel = \Models\Permission::READONLY)
    {
        if ($group->getVisibility() === \Models\Group::VISIBILITY_OPEN && $accessLevel == \Models\Permission::READONLY) {
            return true;
        }

        //check for MEMBER or higher, cause OPEN and READONLY check is done
        if ($accessLevel < \Models\Permission::MEMBER) {
            $accessLevel = \Models\Permission::MEMBER;
        }

        $groupPermission = null;
        $groupId = $group->getId();
        foreach ($this->getPermissions() as $permission) {
            if ($permission->getGroupId() == $groupId) {
                $groupPermission = $permission;
                break;
            }
        }

        return (!empty($groupId) && $groupPermission !== null && $groupPermission->hasAccess($accessLevel));
    }

    /**
     * Get the groupId's for permissions with minimum access level
     * @param int $minimumAccessLevel
     * @return array
     */
    public function getPermissionGroupIds($minimumAccessLevel = \Models\Permission::MEMBER)
    {
        $groupIds = array();
        foreach ($this->getPermissions() as $permission) {
            if ($permission->getAccessLevel() >= $minimumAccessLevel) {
                $groupIds[] = $permission->getGroupId();
            }
        }
        return $groupIds;
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
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->logoutUrl;
    }

    /**
     * @param $logoutUrl
     * @return $this
     */
    public function setLogoutUrl($logoutUrl)
    {
        $this->logoutUrl = $logoutUrl;
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

    /**
     * @ODM\PrePersist
     * @ODM\PreUpdate
     */
    /*public function validate()
    {
        $permissions = $this->getPermissions();
        if (is_object($permissions)) {
            $this->permissions = $permissions->toArray();
        }
    }*/
}