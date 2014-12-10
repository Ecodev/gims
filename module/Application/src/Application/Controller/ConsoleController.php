<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Model\User;

class ConsoleController extends AbstractActionController
{

    use \Application\Traits\EntityManagerAware;

    /**
     * Import data from JMP file
     */
    public function importJmpAction()
    {
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Jmp();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

    /**
     * Import data from JMP file
     */
    public function importGlassAction()
    {
        $importer = new \Application\Service\Importer\Glass();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import();
    }

    /**
     * Import data from population file
     */
    public function importPopulationAction()
    {
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Population();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

    public function cacheClearAction()
    {
        $cache = $this->getServiceLocator()->get('Calculator\Cache');
        $count = $cache->flush();

        return $count . ' keys deleted' . PHP_EOL;
    }

    /**
     * Simulate a user.
     * Should be used with caution because DB modification will be stamped with that user
     * @param User $user
     */
    private function impersonateUser(User $user)
    {
        $sm = \Application\Module::getServiceManager();

        // Override option and re-create roleService with new options
        $sm->setFactory('Application\Service\FakeIdentityProvider', function() {
            return new \Application\Service\FakeIdentityProvider();
        });
        $options = $sm->get('ZfcRbac\Options\ModuleOptions');
        $options->setIdentityProvider('Application\Service\FakeIdentityProvider');
        $factory = new \ZfcRbac\Factory\RoleServiceFactory();
        $roleService = $factory->createService($sm);

        // Override services with our new one
        $sm->setAllowOverride(true);
        $sm->setService('ZfcRbac\Service\RoleService', $roleService);
        $sm->setAllowOverride(false);

        $fakeIdentityProvider = $sm->get('Application\Service\FakeIdentityProvider');
        $fakeIdentityProvider->setIdentity($user);
    }

    public function cacheWarmUpAction()
    {
        $userId = $this->getRequest()->getParam('userId');
        if ($userId != 'anonymous') {
            $user = $this->getEntityManager()->getRepository('Application\Model\User')->findOneById($userId);
            $userName = $user->getName();
        } else {
            $userName = $userId;
        }

        echo 'Warming up cache for user: ' . $userName . PHP_EOL;

        $progress = function($i, $total) {
            $digits = 3;

            return str_pad($i, $digits, ' ', STR_PAD_LEFT) . '/' . str_pad($total, $digits, ' ', STR_PAD_LEFT);
        };

        $geonames = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findBy([], ['id' => 'DESC']);
        $total = count($geonames);

        $i = 1;
        foreach ($geonames as $geoname) {
            echo $progress($i++, $total) . ' ';
            $cmd = 'php htdocs/index.php cache warm-up ' . escapeshellarg($userId) . ' ' . escapeshellarg($geoname->getName());
            system($cmd);
        }

        return 'done' . PHP_EOL;
    }

    public function cacheWarmUpOneAction()
    {
        $userId = $this->getRequest()->getParam('userId');

        if ($userId != 'anonymous') {
            $user = $this->getEntityManager()->getRepository('Application\Model\User')->findOneById($userId);
            $this->impersonateUser($user);
        }

        $geonameName = $this->getRequest()->getParam('geoname');

        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $aggregator = new \Application\Service\Calculator\Aggregator();
        $aggregator->setCalculator($calculator);

        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $filters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findAll();
        $geoname = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findOneByName($geonameName);

        echo $geoname->getName() . PHP_EOL;
        foreach ($parts as $part) {
            echo '        ' . $part->getName() . PHP_EOL;
            $aggregator->computeFlattenAllYears($filters, $geoname, $part);
        }
    }

    public function computePopulationAction()
    {
        $this->getEntityManager()->getRepository('Application\Model\Geoname')->computeAllPopulation();
    }

}
