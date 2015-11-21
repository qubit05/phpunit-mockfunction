PHPUnit Mock Function
=====================

PHPUnit extension to Mock PHP internal functions using Runkit.

Requirements
------------
- [Runkit](https://github.com/zenovich/runkit)
- [Test Helpers*](https://github.com/sebastianbergmann/php-test-helpers/blob/master/test_helpers.c)

*This is an optional requirement. Runkit doesn't currently support the override of internal functions (exit, die etc). 



Installation
------------

Using composer, add the following to the composer.json file:

    {
       "require": {
           "lancerhe/phpunit-mock": "dev-master"
       }
    }


Function Example
-------

`ExampleClass.php`

    <?php
    class ExampleClass {
        public function doExample() {
            return date();
        }
    }

`ExampleClassTest.php`

    <?php
    class ExampleClassTest extends \PHPUnit_Framework_TestCase {
        
        /**
         * @test
         */
        public function return_expected_value() {
            $param = 'Y-m-d H:i:s';
            $value = 'non date value';
        
            $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
            $mockFunction->expects($this->once())
                ->with($this->equalTo($param))
                ->will($this->returnValue($value));
            
            $exampleClass = new ExampleClass();
            $this->assertEquals($value, $exampleClass->doExample($param));
        }
    }

Class Example
-------

`ExampleClass.php`

    <?php
    class Calculate {
        public $a, $b;
        public function __construct($a, $b) {
            $this->a = $a;
            $this->b = $b;
        }
        public function add() {
            return $this->a + $this->b;
        }

        public function minus() {
            return $this->a - $this->b;
        }
    }

`ExampleClassTest.php`

    <?php
    class PHPUnit_Extensions_MockClassTest extends \PHPUnit_Framework_TestCase {

        /**
         * @test
         */
        public function method_return_expected_value() {
            $value = 'non value';

            $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
            $mockClass->expects($this->any())
                ->method('add')
                ->will($this->returnValue($value));

            $this->assertEquals($value, (new Calculate(4, 2))->add());
        }
    }