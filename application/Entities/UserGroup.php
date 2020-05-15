<?php

namespace Entities;

/**
 * @Entity @Table(name="user_groups")
 **/
class UserGroup
{
    
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    
    /**
  * @ManyToOne(targetEntity="User")
  * @JoinColumns({
  *   @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
  * })
  */
    protected $user;
    
    /**
  * @ManyToOne(targetEntity="Group")
  * @JoinColumns({
  *   @JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
  * })
  */
    protected $group;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
}