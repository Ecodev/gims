<?php

namespace Application\Service;

class MultipleRoleContext extends \Doctrine\Common\Collections\ArrayCollection implements \Application\Service\RoleContextInterface
{

    private $grantOnlyIfGrantedByAllContexts;

    public function __construct(array $elements = array(), $grantOnlyIfGrantedByAllContexts = false)
    {
        $this->setGrantOnlyIfGrantedByAllContexts($grantOnlyIfGrantedByAllContexts);
        parent::__construct($elements);
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

}