<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Login
 *
 * @ORM\Table(name="login")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LoginRepository")
 *
 * @Serializer\XmlRoot("login")
 * @Serializer\ExclusionPolicy("all")
 *
 * @Hateoas\Relation(
 *     "edit",
 *     href = @Hateoas\Route(
 *       "edit_login",
 *       parameters = { "loginid" = "expr(object.getId())" }
 *     )
 * )
 */
class Login
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var string
     * @Serializer\Expose()
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Serializer\Expose()
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string
     * @Serializer\Expose()
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * Length of encoded password is ~ 2x number of chars + 170
     * Limit input to 1024 bytes and this field should be enough
     *
     * @var string
     *
     * @Assert\Length(max=2300)
     * @ORM\Column(name="password", type="string", length=2300)
     */
    private $password;

    /**
     * @var string
     * @Serializer\Expose()
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="Groups", inversedBy="logins")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false))
     */
    private $group;


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
     * @return Login
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
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Login
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Login
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Login
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Login
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Groups $group
     *
     * @return Login
     */
    public function setGroup(\AppBundle\Entity\Groups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Groups
     */
    public function getGroup()
    {
        return $this->group;
    }
}
