<?php
/**
 * Mock Function
 */
class PHPUnit_Extensions_MockFunction {

    /**
     * @var array
     */
    static public $mock_objects = array();

    /**
     * @var string
     */
    protected $_function_name;

    /**
     * @var string
     */
    protected $_function_alias;

    /**
     * @var boolean
     */
    protected $_mock = false;

    /**
     * Construct
     *
     * @param string                     $function
     * @param PHPUnit_Framework_TestCase $testcase
     */
    public function __construct($function, PHPUnit_Framework_TestCase $testcase) {
        if ( ! function_exists($function) ) {
            throw new InvalidArgumentException("Invalid function name '$function'");
        }
        if ( array_key_exists($function, self::$mock_objects) ) {
            throw new RuntimeException("Can not create second function mock for '$function'");
        }

        $this->_function_name  = $function;
        $this->_function_alias = uniqid("function_");
        self::$mock_objects[$this->_function_name] = $testcase->getMock($this->_function_alias, array('call'));
        $this->mock();
    }

    /**
     * Mock
     *
     * @return \MockFunction
     */
    public function mock() {
        if ( $this->_mock ) 
            return $this;

        $this->_mock = true;
        runkit_function_rename($this->_function_name, $this->_function_alias);
        runkit_function_add($this->_function_name, '', $this->getMockCode());
        return $this;
    }

    /**
     * Restore
     *
     * @return \MockFunction
     */
    public function restore() {
        if ( ! $this->_mock ) 
            return $this;

        $this->_mock = false;
        runkit_function_remove($this->_function_name);
        runkit_function_rename($this->_function_alias, $this->_function_name);
        return $this;
    }

    /**
     * Destruct
     */
    public function __destruct() {
        unset(self::$mock_objects[$this->_function_name]);
        $this->restore();
    }

    /**
     * Get mock code
     *
     * @return string
     */
    public function getMockCode() {
        $self     = __CLASS__;
        $function = var_export($this->_function_name, true);

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
        return self::getMock($this->_function_name)->expects($matcher)->method('call');
    }

    /**
     * Get mock
     *
     * @param  string $function
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws \RuntimeException
     */
    public static function getMock($function) {
        if ( ! array_key_exists($function, self::$mock_objects) )
            throw new \RuntimeException("Failed to get mock object for function '$function'");
        return self::$mock_objects[$function];
    }
}
