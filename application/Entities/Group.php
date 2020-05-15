<?php

namespace Entities;

/**
 * 
 * @Entity @Table(name="groups")
 * 
 */
class Group
{
 /** @Id @Column(type="integer") @GeneratedValue **/
    private $id;

    /** @Column(name="name", type="text") */
    private $name;

    /** @Column(name="created", type="datetime") */
    private $created;

    /** @Column(name="description", type="text") */
    private $description;


    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
