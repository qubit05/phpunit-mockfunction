<?php
/**
 * MockClass Test
 * @author Lancer He <lancer.he@gmail.com>
 * @since  2015-11-21
 */
/**
 * For test.
 */
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

    public function debug($output) {
        return $output;
    }
}

/**
 * For test.
 */
class Order {
    public $amount = 0;
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    public function getAmount() {
        return $this->amount;
    }
}

class PHPUnit_Extensions_MockClassTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function class_one_method_return_expected_value() {
        $value = 'non value';

        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $mockClass->expects($this->any())
            ->method('add')
            ->will($this->returnValue($value));

        $this->assertEquals($value, (new Calculate(4, 2))->add());
    }

    /**
     * @test
     */
    public function class_two_methods_return_expected_value() {
        $add_value   = 'add value';
        $minus_value = 'minus value';

        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add', 'minus'], $this);
        $mockClass->expects($this->any())
            ->method('add')
            ->will($this->returnValue($add_value));

        $mockClass->expects($this->any())
            ->method('minus')
            ->will($this->returnValue($minus_value));

        $this->assertEquals($add_value, (new Calculate(4, 2))->add());
        $this->assertEquals($minus_value, (new Calculate(4, 2))->minus());
    }

    /**
     * @test
     */
    public function parameter_is_passed_to_method() {
        $output = 'a';

        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['debug'], $this);
        $mockClass->expects($this->once())
            ->method('debug')
            ->with($this->equalTo($output));

        (new Calculate(4, 2))->debug($output);
    }

    /**
     * @test
     */
    public function method_call_twice() {
        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['debug'], $this);
        $mockClass->expects($this->exactly(2))->method('debug');

        $Calculate = new Calculate(4, 2);
        $Calculate->debug();
        $Calculate->debug();
    }

    /**
     * @test
     */
    public function class_restore_return_original_result() {
        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $mockClass->restore();

        $this->assertEquals(6, (new Calculate(4, 2))->add());
    }

    /**
     * @test
     */
    public function class_switch_off_and_on_return_correct_value() {
        $value = 'non value';

        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $mockClass->expects($this->any())
            ->method('add')
            ->will($this->returnValue($value));
        $mockClass->restore();
        $mockClass->mock();

        $this->assertEquals($value, (new Calculate(4, 2))->add());
    }

    /**
     * @test
     */
    public function no_setup_return_null_value() {
        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $this->assertNull((new Calculate(4, 2))->add());
    }
    
    /**
     * @test
     */    
    public function expect_return_correct_object() {
        $expected = 'PHPUnit_Framework_MockObject_Builder_InvocationMocker';
        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $this->assertInstanceOf($expected, $mockClass->expects($this->any())->method('add'));
    }

    /**
     * @test
     */
    public function normal_functionality_is_returned_on_destruct() {
        $mockClass = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        unset($mockClass);
        $this->assertEquals(6, (new Calculate(4, 2))->add());
    }

    /**
     * @test
     */
    public function creating_two_instance_for_different_class() {
        $value = 'non value';
        $mockCalculate = new PHPUnit_Extensions_MockClass('Calculate', ['add'], $this);
        $mockCalculate->expects($this->any())
            ->method('add')
            ->will($this->returnValue($value));

        $value2 = 'testval2';
        $mockOrder = new PHPUnit_Extensions_MockClass('Order', ['getAmount'], $this);
        $mockOrder->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($value2));

        $this->assertEquals($value, (new Calculate(4, 2))->add());
        $this->assertEquals($value2, (new Order)->getAmount());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can not create second class mock
     */
    public function creating_two_instances_for_the_same_class() {
        $mockCalculate1 = new PHPUnit_Extensions_MockClass('Calculate', 'add', $this);
        $mockCalculate2 = new PHPUnit_Extensions_MockClass('Calculate', 'add', $this);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Failed to get mock object
     */
    public function get_mock_with_unknown_class() {
        $mockCalculate = new PHPUnit_Extensions_MockClass('Calculate', 'add', $this);
        PHPUnit_Extensions_MockClass::getMock('Order');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid class name
     */
    public function build_mock_for_invalid_function() {
        $mockClass = new PHPUnit_Extensions_MockClass('some1_bad2_function3_name4_that5_doesnt6_exist7_hopefully8_ever9', 'some_method', $this);
    }
}
