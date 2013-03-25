<?php

namespace Application\Service\Importer;

abstract class AbstractImporter
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;

    /**
     * Returns a part either from database, or newly created
     * @param string $name
     * @return \Application\Model\Part
     */
    protected function getPart($name)
    {
        $partRepository = $this->getEntityManager()->getRepository('Application\Model\Part');
        $part = $partRepository->findOneBy(array('name' => $name));

        if (!$part) {
            $part = new \Application\Model\Part($name);
            $this->getEntityManager()->persist($part);
        }

        return $part;
    }

}
