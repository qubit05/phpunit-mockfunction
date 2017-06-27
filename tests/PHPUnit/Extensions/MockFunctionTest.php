<?php
/**
 * MockFunction Test
 * @since  2015-11-21
 */
class PHPUnit_Extensions_MockFunctionTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function function_return_expected_value() {
        $value = 'non date value';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->any())
            ->will($this->returnValue($value));

        $this->assertEquals($value, date());
    }

    /**
     * @test
     */
    public function parameter_is_passed_to_function() {
        $format = 'Y-m-d H:i:s';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->once())
            ->with($this->equalTo($format));

        date($format);
    }

    /**
     * @test
     */
    public function function_call_twice() {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->exactly(2));

        date();
        date();
    }

    /**
     * @test
     */
    public function function_restore_return_original_functionality() {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->restore();

        $this->assertEquals('2013-04-22', date('Y-m-d', 1366620395));
    }

    /**
     * @test
     */
    public function function_switch_off_and_on_return_correct_value() {
        $value = 'non date value';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->any())
            ->will($this->returnValue($value));
        $mockFunction->restore();
        $mockFunction->mock();

        $this->assertEquals($value, date());
    }

    /**
     * @test
     */
    public function no_setup_return_null_value() {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $this->assertNull(date());
    }
    
    /**
     * @test
     */    
    public function expect_return_correct_object() {
        $expected = 'PHPUnit_Framework_MockObject_Builder_InvocationMocker';
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $this->assertInstanceOf($expected, $mockFunction->expects($this->any()));
    }

    /**
     * @test
     */
    public function normal_functionality_is_returned_on_destruct() {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        unset($mockFunction);
        $this->assertEquals('2013-04-22', date('Y-m-d', 1366620395));
    }

    /**
     * @test
     */
    public function creating_two_instance_for_different_functions() {
        $value1 = 'testval1';
        $mockDate = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockDate->expects($this->once())
            ->will($this->returnValue($value1));

        $value2 = 'testval2';
        $mockTime = new PHPUnit_Extensions_MockFunction('time', $this);
        $mockTime->expects($this->once())
            ->will($this->returnValue($value2));

        $this->assertEquals($value1, date());
        $this->assertEquals($value2, time());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can not create second function mock
     */
    public function creating_two_instances_for_the_same_function() {
        $mockDate1 = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockDate2 = new PHPUnit_Extensions_MockFunction('date', $this);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Failed to get mock object
     */
    public function get_mock_with_unknown_function() {
        $mockDate = new PHPUnit_Extensions_MockFunction('date', $this);
        PHPUnit_Extensions_MockFunction::getMock('time');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid function name
     */
    public function build_mock_for_invalid_function() {
        $mockFunction = new PHPUnit_Extensions_MockFunction('some1_bad2_function3_name4_that5_doesnt6_exist7_hopefully8_ever9', $this);
    }
}
