<?php

namespace Models;

use Gigablah\Silex\OAuth\Security\User\StubUser;
use LoginProvider\UserProviderListener;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Security\Core\Exception\RuntimeException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ODM\Document(
 *     collection="users",
 *     indexes={
 *         @ODM\Index(keys={"userName"="desc"}, options={"unique"=false}),
 *         @ODM\Index(keys={"email"="desc"}, options={"unique"=true})
 *     }
 * )
 * @ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */
class User
{
    /**
     * @ODM\Id(strategy="AUTO")
     * @Accessor(getter="getId",setter="setId")
     * @Type("string")
     */
    private $id;

    /**
     * @ODM\String
     * @Accessor(getter="getUserName",setter="setUserName")
     * @Type("string")
     */
    private $userName;

    /**
     * @ODM\String
     * @Accessor(getter="getEmail",setter="setEmail")
     * @Type("string")
     */
    private $email;

    /**
     * @ODM\Hash
     * @var array
     * @Type("array<string, string>")
     * @Exclude
     */
    private $loginProviderId = array(
        UserProviderListener::SERVICE_FACEBOOK => null,
        UserProviderListener::SERVICE_TWITTER => null,
        UserProviderListener::SERVICE_GOOGLE => null,
        UserProviderListener::SERVICE_GITHUB => null
    );

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
     */
    public function setProviderId($service, $id)
    {
        if (array_key_exists($service, $this->loginProviderId) === false) {
            throw new RuntimeException("No login provider service $service configured.");
        }
        $this->loginProviderId[$service] = $id;
    }

    /**
     * Load user from StubUser
     * @param StubUser $stubUser
     */
    public function loadFromOauthUser(StubUser $stubUser)
    {
        $this->setUserName($stubUser->getUsername());
        $this->setEmail($stubUser->getEmail());
    }
}