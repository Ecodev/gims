<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="Application\Repository\UserRepository")
 */
class User extends AbstractModel implements \ZfcUser\Entity\UserInterface
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $state;

    /**
     * Set name
     *
     * @param string $name
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
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
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
     * Set state
     *
     * @param integer $state
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getUsername();
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function setDisplayName($displayName)
    {
        return $this->setUsername($displayName);
    }

    /**
     * Not implemented
     * @throws \Exception
     */
    public function setId($id)
    {
        // @TODO: Is it really necessary to set the ID ?
        throw new Exception("Not implemented, because Doctrine will set the ID automagically");
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function setUsername($username)
    {
        return $this->setName($username);
    }

}
