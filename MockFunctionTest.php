<?php

require_once './MockFunction.php';

class PHPUnit_Extensions_MockFunctionTest extends PHPUnit_Framework_TestCase
{
    public function testFunctionReturnsExpectedValue()
    {
        $value = 'non date value';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->any())
            ->will($this->returnValue($value));

        $this->assertEquals($value, date());
    }

    public function testParameterIsPassedToFunction()
    {
        $format = 'Y-m-d H:i:s';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->once())
            ->with($this->equalTo($format));

        date($format);
    }

    public function testCallingFunctionTwice()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->exactly(2));

        date();
        date();
    }

    public function testWhenDisabledReturnsOriginalFunctionality()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->disable();
        $this->assertEquals('2013-04-22', date('Y-m-d', 1366620395));
    }

    public function testWhenSwitchOffAndOnReturnsCorrectValue()
    {
        $value = 'non date value';

        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->expects($this->any())
            ->will($this->returnValue($value));
        $mockFunction->disable();
        $mockFunction->enable();

        $this->assertEquals($value, date());
    }

    public function testNoSetupReturnsNullValue()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $this->assertNull(date());
    }

    public function testOriginalFunctionCall()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $this->assertEquals('2013-04-22', $mockFunction->callOriginal('Y-m-d', 1366620395));
    }

    public function testGetOriginalCallbackWhenInactive()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockFunction->disable();
        $this->assertEquals('date', $mockFunction->getOriginalCallback());
    }

    public function testExpectsReturnsCorrectObject()
    {
        $expected = 'PHPUnit_Framework_MockObject_Builder_InvocationMocker';
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        $this->assertInstanceOf($expected, $mockFunction->expects($this->any()));
    }

    public function testNormalFunctionalityIsReturnedOnDestruct()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('date', $this);
        unset($mockFunction);
        $this->assertEquals('2013-04-22', date('Y-m-d', 1366620395));
    }

    public function testCreatingTwoInstanceForDifferentFunctions()
    {
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
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can not create second function mock
     */
    public function testCreatingTwoInstancesForTheSameFunction()
    {
        $mockDate1 = new PHPUnit_Extensions_MockFunction('date', $this);
        $mockDate2 = new PHPUnit_Extensions_MockFunction('date', $this);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Failed to get mock object
     */
    public function testGetMockWithUnknownFunction()
    {
        $mockDate = new PHPUnit_Extensions_MockFunction('date', $this);
        PHPUnit_Extensions_MockFunction::getMock('time');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid function name
     */
    public function testGettingMockForInvalidFunctioName()
    {
        $mockFunction = new PHPUnit_Extensions_MockFunction('some1_bad2_function3_name4_that5_doesnt6_exist7_hopefully8_ever9', $this);
    }
}
