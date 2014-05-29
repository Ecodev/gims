<?php

namespace Application\Service;

class MultipleRoleContext extends \Doctrine\Common\Collections\ArrayCollection implements \Application\Service\RoleContextInterface
{

    private $grantOnlyIfGrantedByAllContexts;

    public function __construct(array $elements = array(), $grantOnlyIfGrantedByAllContexts = false)
    {
        $this->setGrantOnlyIfGrantedByAllContexts($grantOnlyIfGrantedByAllContexts);
        parent::__construct();

        // Keep things unique
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * @return boolean
     */
    public function getGrantOnlyIfGrantedByAllContexts()
    {
        return $this->grantOnlyIfGrantedByAllContexts;
    }

    /**
     * @param mixed $grantOnlyIfGrantedByAllContexts
     * @return \Application\Service\MultipleRoleContext
     */
    public function setGrantOnlyIfGrantedByAllContexts($grantOnlyIfGrantedByAllContexts)
    {
        $this->grantOnlyIfGrantedByAllContexts = $grantOnlyIfGrantedByAllContexts;

        return $this;
    }

    public function getId()
    {
        throw new \Exception('Not implemented');
    }

    public function getName()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Override parent to ensure unicity of elements
     * @param mixed $value
     * @return boolean
     */
    public function add($value)
    {
        if (!$this->contains($value)) {
            parent::add($value);
        }

        return true;
    }

}
