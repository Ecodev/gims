<?php

namespace Application\Service;

class MultipleRoleContext extends \Doctrine\Common\Collections\ArrayCollection implements \Application\Service\RoleContextInterface
{

    /**
     * @var boolean
     */
    private $grantOnlyIfGrantedByAllContexts;

    /**
     * Constructor
     * @param array $contexts
     * @param boolean $grantOnlyIfGrantedByAllContexts
     */
    public function __construct($contexts = [], $grantOnlyIfGrantedByAllContexts = true)
    {
        parent::__construct();

        $this->grantOnlyIfGrantedByAllContexts = $grantOnlyIfGrantedByAllContexts;
        $this->merge($contexts);
    }

    /**
     * @return boolean
     */
    public function getGrantOnlyIfGrantedByAllContexts()
    {
        return $this->grantOnlyIfGrantedByAllContexts;
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
     * Merge contexts into existing one. This is the prefered way of adding contexts.
     * @param null|RoleContextInterface|RoleContextInterface[]|MultipleRoleContext $contexts
     */
    public function merge($contexts)
    {
        if ($contexts) {
            if ($contexts instanceof \Application\Model\AbstractModel) {
                $contexts = [$contexts];
            }

            foreach ($contexts as $context) {
                $this->add($context);
            }
        }
    }

    /**
     * Override parent to ensure unicity of elements
     * @param mixed $value
     * @return boolean always true
     */
    public function add($value)
    {
        if (!$value) {
            return true;
        }

        if (!$value instanceof RoleContextInterface) {
            throw new \Exception('Must be a RoleContextInterface');
        }

        if (!$this->contains($value)) {
            parent::add($value);
        }

        return true;
    }

}
