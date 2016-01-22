<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */

    protected $name;


    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user")
     */

    protected $groups;

    /**
     * @ORM\Column(name="pubKey", type="string", length=500)
     */
    protected $pubKey;

    /**
     * @ORM\Column(name="privateKey", type="string", length=2000)
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
}
