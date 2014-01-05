<?php
namespace Models;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\RuntimeException;

/**
 * @ODM\Document(
 *     collection="profiles",
 *     indexes={
 *         @ODM\Index(keys={"interests"="desc"}, options={"unique"=false})
 *     },
 *     repositoryClass="\Models\Profile"
 * )
 * @JMS\ExclusionPolicy("none")
 *
 * @see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html?highlight=annotations#document
 */

class Profile extends BaseModel{
    /**
     * @ODM\Id(strategy="AUTO")
     * @JMS\Accessor(getter="getId",setter="setId")
     * @JMS\Type("string")
     * @JMS\Readonly
     */
    protected $id;

    /**
     * @ODM\Collection
     * @var array
     * @JMS\Accessor(getter="getInterests",setter="setInterests")
     * @JMS\Type("array<array>")
     * @JMS\Groups({"profile-detail", "profile-update", "user-profile"})
     */
    protected $interests = array();
    /**
     * @ODM\Date
     * @JMS\Accessor(getter="getFormattedBirthday",setter="setBirthday")
     * @JMS\Type("string")
     * @JMS\Groups({"profile-detail", "profile-update", "user-profile"})
     */
    private $birthday;
    /**
     * @ODM\String
     * @JMS\Accessor(getter="getAbout",setter="setAbout")
     * @JMS\Type("string")
     * @JMS\Groups({"profile-detail", "profile-update", "user-profile"})
     */
    private $about;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return \Models\Profile
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthday(){
        return $this->birthday;
    }
    /**
     * @return string
     */
    public function getFormattedBirthday(){
        if (!$this->birthday) {
            return $this->birthday;
        }
        return $this->birthday->format('Y-m-d\TH:i:s+');
    }
    /**
     * @param $birthday
     * @return \Models\Profile
     */
    public function setBirthday($birthday){
        $this->birthday= $birthday;
        return $this;
    }
    /**
     * @return string
     */
    public function getAbout(){
        return $this->about;
    }
    /**
     * @param $about
     * @return \Models\Profile
     */
    public function setAbout($about){
        return $this->about = $about;
    }

    /**
     * @return array
     */
    public function getInterests(){
        return $this->interests;
    }
    /**
     * @param $interests
     * @return \Models\Profile
     */
    public function setInterests(array $interests){
        $this->interests = $interests;
        return $this;
    }
    /**
     * @param $interest
     * @return \Models\Profile
     */
    public function addInterest(array $interest){
        $this->interests[] = $interest;
        return $this;
    }
}