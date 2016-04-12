<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group as BaseGroup;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Groups
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupsRepository")
 * @UniqueEntity("name")
 */
class Groups extends BaseGroup
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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="group")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="Login", mappedBy="group")
     */
    private $logins;

    /**
     * @ORM\OneToMany(targetEntity="Groups", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Groups", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;
    // TODO when setting parent, ensure that it's not one of our children

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->users = new ArrayCollection();
        $this->logins = new ArrayCollection();
        $this->children = new ArrayCollection();

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
     * @return Groups
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
     * Add user
     *
     * @param \AppBundle\Entity\UserGroup $user
     *
     * @return Groups
     */
    public function addUser(\AppBundle\Entity\UserGroup $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\UserGroup $user
     */
    public function removeUser(\AppBundle\Entity\UserGroup $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add login
     *
     * @param \AppBundle\Entity\Login $login
     *
     * @return Groups
     */
    public function addLogin(\AppBundle\Entity\Login $login)
    {
        $this->logins[] = $login;

        return $this;
    }

    /**
     * Remove login
     *
     * @param \AppBundle\Entity\Login $login
     */
    public function removeLogin(\AppBundle\Entity\Login $login)
    {
        $this->logins->removeElement($login);
    }

    /**
     * Get logins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogins()
    {
        return $this->logins;
    }

    /**
     * Get groupKey
     *
     * @return binary
     */
    public function getGroupKey()
    {
        return $this->groupKey;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Groups
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Checks if ID is a child of this Entity
     */
    public function childExists($entity)
    {
        // Check if the entity is in children, else recurse into children to check
        if ($this->children->contains($entity)) {
            return true;
        } else {
            foreach ($this->children as $child) {
                if ($child->childExists($entity)) return true;
            }
        }
        return false;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Groups $child
     *
     * @return Groups
     */
    public function addChild(\AppBundle\Entity\Groups $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Groups $child
     */
    public function removeChild(\AppBundle\Entity\Groups $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Groups $parent
     *
     * @return Groups
     */
    public function setParent(\AppBundle\Entity\Groups $parent = null)
    {
        if($this->childExists($parent)) {
            throw new \Exception("Attempt to set child as parent");
        }
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Groups
     */
    public function getParent()
    {
        return $this->parent;
    }
}
