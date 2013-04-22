<?php

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
     * @var string
     */
    protected $functionName;

    /**
     * @var string
     */
    protected $functionAlias;

    /**
     * @var boolean
     */
    protected $active = false;

    /**
     * Construct
     *
     * @param string $functionName
     * @param PHPUnit_Framework_TestCase $testCase
     */
    public function __construct($functionName, PHPUnit_Framework_TestCase $testCase)
    {
        $this->functionName  = $functionName;
        $this->functionAlias = uniqid("{$functionName}_");

        if (!function_exists($functionName)) {
            throw new InvalidArgumentException("Invalid function name '$functionName'");
        }
        if (array_key_exists($functionName, self::$mockObjects)) {
            throw new RuntimeException("Can not create second function mock for '$functionName'");
        }
        self::$mockObjects[$functionName] = $testCase->getMock($this->functionAlias, array('call'));

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
            runkit_function_rename($this->functionName, $this->functionAlias);
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
            runkit_function_rename($this->functionAlias, $this->functionName);
        }

        return $this;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        unset(self::$mockObjects[$this->functionName]);
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
            $functionName = $this->functionAlias;
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
        $className    = __CLASS__;
        $functionName = var_export($this->functionName, true);

        return "
            \$callback = array($className::getMock($functionName), 'call');
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
        return self::getMock($this->functionName)->expects($matcher)->method('call');
    }

    /**
     * Get mock
     *
     * @param string $functionName
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws \RuntimeException
     */
    public static function getMock($functionName)
    {
        if (!array_key_exists($functionName, self::$mockObjects)) {
            throw new \RuntimeException("Failed to get mock object for function '$functionName'");
        }

        return self::$mockObjects[$functionName];
    }
}
