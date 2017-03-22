<?php

/**
 * Mock Class
 */
class PHPUnit_Extensions_MockClass {

    /**
     * @var array
     */
    static public $mockObjects = [];

    /**
     * @var string
     */
    protected $_className;

    /**
     * @var string
     */
    protected $_classAlias;

    /**
     * @var array
     */
    protected $_methods;

    /**
     * @var boolean
     */
    protected $_mock = false;

    /**
     * PHPUnit_Extensions_MockClass constructor.
     * @param $class
     * @param $methods
     * @param PHPUnit_Framework_TestCase $testCase
     */
    public function __construct($class, $methods, PHPUnit_Framework_TestCase $testCase) {
        if ( ! class_exists($class) ) {
            throw new InvalidArgumentException("Invalid class name '$class'");
        }
        if ( array_key_exists($class, self::$mockObjects) ) {
            throw new RuntimeException("Can not create second class mock for '$class'");
        }

        $this->_className  = $class;
        $this->_classAlias = uniqid("class_");
        $this->_methods    = is_string($methods) ? [$methods] : $methods;
        $mockBuilder       = $testCase->getMockBuilder($this->_classAlias);
        $mockBuilder->setMethods($this->_methods);
        self::$mockObjects[$this->_className] = $mockBuilder->getMock();
        $this->mock();
    }

    /**
     * @return PHPUnit_Extensions_MockClass
     */
    public function mock() {
        if ( $this->_mock ) return $this;

        $this->_mock = true;
        foreach ( $this->_methods as $method ) {
            runkit_method_rename($this->_className, $method, "_origin_" . $method);
            runkit_method_add($this->_className, $method, '', $this->getMockCode($method));
        }
        return $this;
    }

    /**
     * @return PHPUnit_Extensions_MockClass
     */
    public function restore() {
        if ( ! $this->_mock ) return $this;

        $this->_mock = false;
        foreach ( $this->_methods as $method ) {
            runkit_method_remove($this->_className, $method);
            runkit_method_rename($this->_className, "_origin_" . $method, $method);
        }
        return $this;
    }

    /**
     * Destruct
     */
    public function __destruct() {
        unset(self::$mockObjects[$this->_className]);
        $this->restore();
    }

    /**
     * Get mock code
     *
     * @param  string $method
     * @return string
     */
    public function getMockCode($method) {
        $self       = __CLASS__;
        $class_name = var_export($this->_className, true);
        $method     = var_export($method, true);

        return "
            \$callback = array($self::getMock($class_name), $method);
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
        return self::getMock($this->_className)->expects($matcher);
    }

    /**
     * Get mock
     *
     * @param  string $class
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws \RuntimeException
     */
    public static function getMock($class) {
        if ( ! array_key_exists($class, self::$mockObjects) ) throw new \RuntimeException("Failed to get mock object for function '$class'");
        return self::$mockObjects[$class];
    }
}