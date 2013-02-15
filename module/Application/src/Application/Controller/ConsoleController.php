<?php

namespace Application\Controller;

use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\Adapter;

class ConsoleController extends AbstractActionController
{

    protected $sqlPath = 'data/migrations/'; // This is the path where all SQL patches resides

    /**
     * Returns the last version available in SQL file
     * @return integer last version of patches
     */

    protected function getPatchVersion()
    {
        $lastVersion = 0;
        $d = dir($this->sqlPath);
        while (false !== ($entry = $d->read())) {
            if (preg_match('/^version\.(\d+)\.sql$/i', $entry, $a)) {
                if ((int) $a[1] > $lastVersion)
                    $lastVersion = (int) $a[1];
            }
        }
        $d->close();

        return $lastVersion;
    }

    /**
     * Returns the whole SQL (enclosed in transaction) needed to update from 
     * specified version to specified target version.
     * @param integer $currentVersion the version currently found in database
     * @param integer $targetVersion the target version to reach wich patches 
     * @return string the SQL 
     */
    protected function buildSQL($currentVersion, $targetVersion)
    {

        if ($currentVersion > $targetVersion)
            throw new \RuntimeException('Cannot downgrade versions. Target version must be higher than current version');

        $missingVersions = array();
        $sql = '';
        for ($v = $currentVersion + 1; $v <= $targetVersion; $v++) {
            $file = $this->sqlPath . 'version.' . str_pad($v, 3, '0', STR_PAD_LEFT) . '.sql';
            if (is_file($file)) {
                $sql .= "\n-- -------- VERSION $v BEGINS ------------------------\n";
                $sql .= file_get_contents($file);
                $sql .= "\n-- -------- VERSION $v ENDS --------------------------\n";
            } else {
                $missingVersions[] = $v;
            }
        }

        if (count($missingVersions))
            throw new \RuntimeException('Missing SQL file for versions: ' . join(',', $missingVersions));

        return $sql;
    }

    /**
     * Executes a batch of SQL commands.
     * @param string $sql to be executed
     * @return string the error code returned by mysql 
     */
    protected function executeBatchSql($sql, $version)
    {
        /* @var $db \Zend\Db\Adapter\Adapter */
        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $affectedRows = 0;

        // Strip lines with comments starting at beginning of line
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        // Split SQL queries with ';'
        preg_match_all('/([^;"\']+|"([^"]*)"|\'([^\']*)\')+(;|\s*$)/', $sql, $m);
        $queries = $m[0];

        try {
            $db->driver->getConnection()->beginTransaction();
            foreach ($queries as $query) {
                if (strlen(trim($query)) > 0) {
                    echo '.';
                    $result = $db->query($query, Adapter::QUERY_MODE_EXECUTE);
                    $affectedRows += $result->getAffectedRows();
                }
            }
            
            $db->driver->getConnection()->commit();
        } catch (\Exception $e) {
            $db->driver->getConnection()->rollback();
            throw new \Exception("FAILED update to version $version ! see error above, the update have been rolled back", null, $e);
        }

        echo "\n" . 'affected rows count: ' . $affectedRows . "\n";
    }

    /**
     * Do the actual update
     */
    public function databaseUpdateAction()
    {
        /**
         * Enforce valid console request
         */
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $sm = $this->getServiceLocator();
        $settingMapper = $sm->get('Application\Mapper\SettingMapper');


        $databaseVersion = $settingMapper->fetch('databaseVersion');
        if ($databaseVersion->value == null)
            $databaseVersion->value = -1;

        $targetVersion = $this->getPatchVersion();

        echo 'current version is: ' . $databaseVersion->value . "\n";
        echo 'target version is : ' . $targetVersion . "\n";

        if ($databaseVersion->value == $targetVersion) {
            echo "already up-to-date\n";
            return;
        }

        for ($v = $databaseVersion->value + 1; $v <= $targetVersion; $v++) {
            $sql = $this->buildSQL($v - 1, $v);
            echo "\n_________________________________________________\n";
            echo "updating to version $v...\n";
            $this->executeBatchSql($sql, $v);
            echo "\nsuccessful update to version $v !\n";
            $databaseVersion->value = $v;
            $databaseVersion->save();
        }
    }
}
