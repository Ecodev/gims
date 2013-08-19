<?php

require_once(__DIR__ . '/../bin/reset_database.php');

/**
 * Load database dump at the very beginning of PHPUnit workflow
 */
class DatabaseMounter implements PHPUnit_Framework_TestListener
{

    private $dbMounted = false;

    /**
     * Load database dump once and only once at the very beginning of all tests
     * @param \PHPUnit_Framework_TestSuite $suite
     * @return void
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->dbMounted)
            return;

        $previousCWD = getcwd();

        chdir(dirname(__DIR__));
        ResetDatabase::loadDump(__DIR__ . '/../', __DIR__ . '/data/db.backup.gzip');
        chdir($previousCWD);

        $this->dbMounted = true;
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        // nothing to do
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        // nothing to do
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        // nothing to do
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        // nothing to do
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        // nothing to do
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        // nothing to do
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        // nothing to do
    }

}
