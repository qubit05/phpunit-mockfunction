PHPUnit Mock Function
=====================

Intro TODO

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
           "qubit05/phpunit-mockfunction": "dev-master"
       },
       "minimum-stability": "dev",
       "repositories": [{
           "type": "vcs",
           "url": "https://github.com/qubit05/phpunit-mockfunction"
       }]
    }


Example
-------

`ExampleClass.php`

    <?php
    class ExampleClass
    {
        public function doExample()
        {
            return date();
        }
    }

`ExampleClassTest.php`

    <?php
    class ExampleClassTest extends \PHPUnit_Framework_TestCase
    {
        public function testExample()
        {
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
    
Acknowledgement
---------------
When this class was created, some inspiration was taken from [tcz/phpunit-mockfunction](https://github.com/tcz/phpunit-mockfunction/blob/master/PHPUnit/Extensions/MockFunction.php). The two classes are similar but not the same.