<?php

namespace Entities;

/**
 *
 * @Entity @Table(name="users")
 * 
 */
class User
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    private $id;

    /** @Column(name="email", type="text") */
    private $email;
    
    /** @Column(name="first_name", type="text") */
    private $first_name;
    
    /** @Column(name="last_name", type="text") */
    private $last_name;
    
    /** @Column(name="password", type="text") */
    private $password;

    /** @Column(name="created", type="datetime") */
    private $created;

    /** @Column(name="description", type="text") */
    private $description;


    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }
    
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }
    
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getLastName()
    {
        return $this->last_name;
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
    
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
