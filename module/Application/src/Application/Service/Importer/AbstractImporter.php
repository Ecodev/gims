<?php

namespace Application\Service\Importer;

abstract class AbstractImporter
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;
}
