<?php

namespace Api\Controller;

use Application\Model\AbstractModel;

class FilterSetController extends AbstractRestfulController
{

    public function postCreate(AbstractModel $newFilterSet, array $data)
    {
        /*
         * Give "Filter editor" role to the user on the new survey, so he can
         * modify filterset, create filters etc..
         */
        $user = $this->getAuth()->getIdentity();
        $role = $this->getEntityManager()->getRepository(\Application\Model\Role::class)->findOneByName('Filter editor');
        $userFilterSet = new \Application\Model\UserFilterSet();
        $userFilterSet->setUser($user)->setFilterSet($newFilterSet)->setRole($role);
        $this->getEntityManager()->persist($userFilterSet);
        $this->getEntityManager()->flush();
    }
}
