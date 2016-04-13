<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\Criteria;
use AppBundle\Entity\UserGroup;
use Avanzu\AdminThemeBundle\Model\UserInterface as ThemeUser;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * User
 *
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 *
 * @Serializer\XmlRoot("user")
 * @Serializer\ExclusionPolicy("all")
 *
 * @Hateoas\Relation(
 *     "edit",
 *     href = @Hateoas\Route(
 *       "edit_login",
 *       parameters = { "loginid" = "expr(object.getId())" }
 *     )
 * )
 *
 */
class User extends BaseUser implements ThemeUser
{
    /**
     * @var int
     *
     * @Serializer\Expose()
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Expose()
     */

    protected $name;


    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user", fetch="EAGER")
     *
     * @Serializer\Exclude()
     *
     */

    protected $groups;

    /**
     * @ORM\Column(name="pubKey", type="string", length=500)
     */
    protected $pubKey = null;

    /**
     * @ORM\Column(name="privateKey", type="string", length=2000, nullable=true)
     */ 
    protected $privateKey;



    public function __construct()
    {
        parent::__construct();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();

    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ? $this->name : $this->getUsername();
    }

    /**
     * Set pubKey
     *
     * @param string $pubKey
     *
     * @return User
     */
    public function setPubKey($pubKey)
    {
        $this->pubKey = $pubKey;

        return $this;
    }

    /**
     * Get pubKey
     *
     * @return string
     */
    public function getPubKey()
    {
        return $this->pubKey;
    }

    /**
     * Set privateKey
     *
     * @param string $privateKey
     *
     * @return User
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Get privateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }


    /**
     * @deprecated as all UserGroup relations can not have a null groupKey
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupsWithoutKeys()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('groupKey'));

        return $this->getGroups()->matching($criteria);
    }

    /**
     * @deprecated as all UserGroup relations can not have a null groupKey
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupsWithKeys()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('groupKey', null));

        return $this->getGroups()->matching($criteria);
    }

    public function getAvatar() {
        return 'https://secure.gravatar.com/avatar/' . md5( trim( $this->getEmailCanonical() ) ) . '?d=retro';
    }
    public function getMemberSince()
    {
        return null;
    }
    public function isOnline() {
        return null;
    }
    public function getIdentifier() {
        return null;
    }
    public function getTitle() {
        return null;
    }

    /* Override FOSUB */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->username = $email;

        return $this;
    }
    public function setEmailCanonical($emailCanonical)
    {
        parent::setEmailCanonical($emailCanonical);
        $this->usernameCanonical = $emailCanonical;

        return $this;
    }
}
