<?php

namespace Application\Service\Calculator;

abstract class AbstractCalculator
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;
}
