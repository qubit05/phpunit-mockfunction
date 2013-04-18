<?php

// https://github.com/zenovich/runkit
//
// https://github.com/tcz/phpunit-mockfunction/blob/master/PHPUnit/Extensions/MockFunction.php
//
// Extention Fail, it doesn't support override of internal functions
// https://github.com/sebastianbergmann/php-test-helpers/blob/master/test_helpers.c

/**
 * Mock Function
 */
class PHPUnit_Extensions_MockFunction
{

    /**
     * @var array
     */
    static protected $mockObjects = array();

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var string
     */
    protected $functionName;

    /**
     * @var string
     */
    protected $mockIdentifier;

    /**
     * Construct
     *
     * @param string $functionName
     * @param PHPUnit_Framework_TestCase $testCase
     */
    public function __construct($functionName, PHPUnit_Framework_TestCase $testCase)
    {
        $this->functionName   = $functionName;
        $this->mockIdentifier = uniqid("{$functionName}_");

        $mockObject = $testCase->getMock($this->mockIdentifier, array('call'));
        self::$mockObjects[$this->mockIdentifier] = $mockObject;

        $this->enable();
    }

    /**
     * Enable
     *
     * @return \MockFunction
     */
    public function enable()
    {
        if (!$this->active) {
            $this->active = true;
            runkit_function_rename($this->functionName, $this->mockIdentifier);
            runkit_function_add($this->functionName, '', $this->getMockCode());
        }

        return $this;
    }

    /**
     * Disable
     *
     * @return \MockFunction
     */
    public function disable()
    {
        if ($this->active) {
            $this->active = false;
            runkit_function_remove($this->functionName);
            runkit_function_rename($this->mockIdentifier, $this->functionName);
        }

        return $this;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        unset(self::$mockObjects[$this->mockIdentifier]);
        $this->disable();
    }

    /**
     * Get original callback
     *
     * @return string
     */
    public function getOriginalCallback()
    {
        if ($this->active) {
            $functionName = $this->mockIdentifier;
        } else {
            $functionName = $this->functionName;
        }

        return $functionName;
    }

    /**
     * Call original
     *
     * @return mixed
     */
    public function callOriginal()
    {
        $callback = $this->getOriginalCallback();

        return call_user_func_array($callback, func_get_args());
    }

    /**
     * Get mock code
     *
     * @return string
     */
    public function getMockCode()
    {
        $className      = __CLASS__;
        $mockIdentifier = var_export($this->mockIdentifier, true);

        return "
            \$callback = array($className::getMock($mockIdentifier), 'call');
            return call_user_func_array(\$callback, func_get_args());
        ";
    }

    /**
     * Registers a new expectation in the mock object and returns the match
     * object which can be infused with further details.
     *
     * @param  PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return self::getMock($this->mockIdentifier)->expects($matcher)->method('call');
    }

    /**
     * Get mock
     *
     * @param string $mockIdentifier
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws \RuntimeException
     */
    static public function getMock($mockIdentifier)
    {
        if (!array_key_exists($mockIdentifier, self::$mockObjects)) {
            throw new \RuntimeException("Failed to get mock object for provided mockIdentifier ($mockIdentifier)");
        }

        return self::$mockObjects[$mockIdentifier];
    }
}
