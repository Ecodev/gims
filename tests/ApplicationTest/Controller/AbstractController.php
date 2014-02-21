<?php

namespace ApplicationTest\Controller;

use Zend\Json\Json;
use \ApplicationTest\Traits\TestWithTransaction;

class AbstractController extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    /**
     * @var array [classname => [id => object]]
     */
    private $identity = array();

    use TestWithTransaction {
        TestWithTransaction::setUp as setUpTransaction;
    }

    public function setUp()
    {
        // Everything is relative to the application root now.
        chdir(__DIR__ . '/../../../');

        // Config is the normal configuration, overridden by test configuration
        $config = include 'config/application.config.php';
        $config['module_listener_options']['config_glob_paths'][] = 'config/autoload/{,*.}{phpunit}.php';

        $this->setApplicationConfig($config);

        parent::setUp();

        // Don't forget to call trait's method
        $this->setUpTransaction();
    }

    /**
     * Asserts that response is valid JSON an returns it as an array
     * @return array
     * @throws \Zend\Json\Exception\RuntimeException
     */
    protected function getJsonResponse()
    {
        $content = $this->getResponse()->getContent();
        try {
            $json = Json::decode($content, Json::TYPE_ARRAY);
        } catch (\Zend\Json\Exception\RuntimeException $exception) {
            throw new \Zend\Json\Exception\RuntimeException($exception->getMessage() . PHP_EOL . PHP_EOL . $content . PHP_EOL, $exception->getCode(), $exception);
        }

        $this->assertTrue(is_array($json), 'server response is not JSON');

        return $json;
    }

    /**
     * Assert JSON are identical with numerical arbitrary precision
     * @param string $expected pretty JSON string with numeric values as float
     * @param string $actual non-pretty JSON string with numeric values as float
     * @param string $message
     * @param string $logFile
     */
    protected function assertNumericJson($expected, $actual, $message, $logFile)
    {
        // Log raw string first, if JSON decode fails
        if ($logFile) {
            file_put_contents($logFile, $actual);
        }

        // Make actual JSON pretty-printed, but without losing any numeric precision
        $actualWithString = \Application\View\Model\NumericJsonModel::numericToString($actual);
        $actualObject = Json::decode($actualWithString, Json::TYPE_ARRAY);
        $prettyActualWithString = json_encode($actualObject, JSON_PRETTY_PRINT);
        $prettyActualWithFloat = \Application\View\Model\NumericJsonModel::stringToNumeric($prettyActualWithString);

        // Overwrite log  with given JSON to file for easy comparaison/replacement of existing expected JSON files
        if ($logFile) {
            file_put_contents($logFile, $prettyActualWithFloat);
        }

        $this->assertSame($expected, $prettyActualWithFloat, $message);
    }

    /**
     * Return a new model from given class
     * This is meant for in-memory tests and should never be used with actual database
     * @param string $classname
     * @return AbstractModel
     */
    protected function getNewModelWithId($classname, $mockedMethods = array())
    {
        if (!isset($this->identity[$classname]))
            $this->identity[$classname] = array();

        $id = count($this->identity[$classname]) + 1;
        $mockedMethods ['getId'] = $this->returnValue($id);

        // Create a stub for the class with fake ID, so we don't have to mess with database
        $stub = $this->getMock($classname, array_keys($mockedMethods));
        foreach ($mockedMethods as $methodName => $methodReturnValue) {

            $stub->expects($this->any())
                    ->method($methodName)
                    ->will($methodReturnValue);
        }

        $this->identity[$classname][$id] = $stub;

        return $stub;
    }

    /**
     * Returns an existing object with the given ID
     * This is meant for in-memory tests and should never be used with actual database
     * @param string $classname
     * @param integer|\Closure $id if closure, return the first object where closure returns true
     * @return type
     */
    protected function getModel($classname, $id)
    {
        if (is_callable($id)) {
            foreach ($this->identity[$classname] as $object) {
                if ($id($object)) {
                    return $object;
                }
            }

            return null;
        } else {
            return $this->identity[$classname][$id];
        }
    }

}
