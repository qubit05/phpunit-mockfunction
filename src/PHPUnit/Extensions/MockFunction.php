<?php

/**
 * Mock Function
 */
class PHPUnit_Extensions_MockFunction {

    /**
     * @var array
     */
    static public $mockObjects = [];

    /**
     * @var string
     */
    protected $_functionName;

    /**
     * @var string
     */
    protected $_functionAlias;

    /**
     * @var boolean
     */
    protected $_mock = false;

    /**
     * Construct
     *
     * @param string $function
     * @param PHPUnit_Framework_TestCase $testCase
     */
    public function __construct($function, PHPUnit_Framework_TestCase $testCase) {
        if ( ! function_exists($function) ) {
            throw new InvalidArgumentException("Invalid function name '$function'");
        }
        if ( array_key_exists($function, self::$mockObjects) ) {
            throw new RuntimeException("Can not create second function mock for '$function'");
        }

        $this->_functionName  = $function;
        $this->_functionAlias = uniqid("function_");
        $mockBuilder          = $testCase->getMockBuilder($this->_functionAlias);
        $mockBuilder->setMethods(["call"]);
        self::$mockObjects[$this->_functionName] = $mockBuilder->getMock();
        $this->mock();
    }

    /**
     * @return PHPUnit_Extensions_MockFunction
     */
    public function mock() {
        if ( $this->_mock ) return $this;

        $this->_mock = true;
        runkit_function_rename($this->_functionName, $this->_functionAlias);
        runkit_function_add($this->_functionName, '', $this->getMockCode());
        return $this;
    }

    /**
     * @return PHPUnit_Extensions_MockFunction
     */
    public function restore() {
        if ( ! $this->_mock ) return $this;

        $this->_mock = false;
        runkit_function_remove($this->_functionName);
        runkit_function_rename($this->_functionAlias, $this->_functionName);
        return $this;
    }

    /**
     * Destruct
     */
    public function __destruct() {
        unset(self::$mockObjects[$this->_functionName]);
        $this->restore();
    }

    /**
     * Get mock code
     *
     * @return string
     */
    public function getMockCode() {
        $self     = __CLASS__;
        $function = var_export($this->_functionName, true);

        return "
            \$callback = array($self::getMock($function), 'call');
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
    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher) {
        return self::getMock($this->_functionName)->expects($matcher)->method('call');
    }

    /**
     * Get mock
     *
     * @param  string $function
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws \RuntimeException
     */
    public static function getMock($function) {
        if ( ! array_key_exists($function, self::$mockObjects) ) throw new \RuntimeException("Failed to get mock object for function '$function'");
        return self::$mockObjects[$function];
    }
}
