<?php

namespace Entities;

/**
 * @Entity @Table(name="group_privileges")
 **/
class GroupPrivilege
{
    
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    
    /**
  * @ManyToOne(targetEntity="Group")
  * @JoinColumns({
  *   @JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
  * })
  */
    protected $group;
    
    
    /**
  * @ManyToOne(targetEntity="Action")
  * @JoinColumns({
  *   @JoinColumn(name="action_id", referencedColumnName="id", nullable=false)
  * })
  */
    protected $action;

    public function getId()
    {
        return $this->id;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
    
    public function getAction()
    {
        return $this->action;
    }

    public function setAction(Action $action)
    {
        $this->action = $action;
    }
}