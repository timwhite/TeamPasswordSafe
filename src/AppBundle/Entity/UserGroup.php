<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserGroup
 *
 * @ORM\Table(name="user_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserGroupRepository")
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
     * @ORM\Column(name="groupKey", type="binary", length=256, nullable=true)
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
        $this->groupKey = $groupKey;

        return $this;
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
}
