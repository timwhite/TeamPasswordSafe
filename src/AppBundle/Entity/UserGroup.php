<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * UserGroup
 *
 * @ORM\Table(name="user_group", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_group_idx", columns={"user_id", "group_id"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserGroupRepository")
 * @UniqueEntity({"user", "group"})
 */
class UserGroup
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="groupKey", type="string", length=1024, nullable=true)
     */
    private $groupKey;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="groups")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false))
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Groups", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false))
     */
    private $group;

    /**
     * @ORM\Column(name="adminAccess", type="boolean")
     */
    private $adminAccess = false;

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
     * Set groupKey
     *
     * @param binary $groupKey
     *
     * @return UserGroup
     */
    public function setGroupKey($groupKey)
    {
        $this->groupKey = base64_encode($groupKey);

        return $this;
    }

    /**
     * Get groupKey
     *
     * @return binary
     */
    public function getGroupKey()
    {
        if($this->groupKey) {
            return base64_decode($this->groupKey);
        }
        return $this->groupKey;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserGroup
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Groups $group
     *
     * @return UserGroup
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

    public function getRoles() {
        return ['ROLE_USER'];
    }

    /**
     * Set adminAccess
     *
     * @param boolean $adminAccess
     *
     * @return UserGroup
     */
    public function setAdminAccess($adminAccess)
    {
        $this->adminAccess = $adminAccess;

        return $this;
    }

    /**
     * Get adminAccess
     *
     * @return boolean
     */
    public function getAdminAccess()
    {
        return $this->adminAccess;
    }
}
