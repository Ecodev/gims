<?php

namespace Application\Traits;

trait EntityManagerAware
{

    /**
     * Get EntityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
}
