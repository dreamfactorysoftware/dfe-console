<?php
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState enabled
 */
class Issue1335Test extends PHPUnit_Framework_TestCase
{
<<<<<<< HEAD
    public function testGlobalString()
=======
    function testGlobalString()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals("Hello", $GLOBALS['globalString']);
    }

<<<<<<< HEAD
    public function testGlobalIntTruthy()
=======
    function testGlobalIntTruthy()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(1, $GLOBALS['globalIntTruthy']);
    }

<<<<<<< HEAD
    public function testGlobalIntFalsey()
=======
    function testGlobalIntFalsey()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(0, $GLOBALS['globalIntFalsey']);
    }

<<<<<<< HEAD
    public function testGlobalFloat()
=======
    function testGlobalFloat()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(1.123, $GLOBALS['globalFloat']);
    }

<<<<<<< HEAD
    public function testGlobalBoolTrue()
=======
    function testGlobalBoolTrue()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(true, $GLOBALS['globalBoolTrue']);
    }

<<<<<<< HEAD
    public function testGlobalBoolFalse()
=======
    function testGlobalBoolFalse()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(false, $GLOBALS['globalBoolFalse']);
    }

<<<<<<< HEAD
    public function testGlobalNull()
=======
    function testGlobalNull()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(null, $GLOBALS['globalNull']);
    }

<<<<<<< HEAD
    public function testGlobalArray()
=======
    function testGlobalArray()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(array("foo"), $GLOBALS['globalArray']);
    }

<<<<<<< HEAD
    public function testGlobalNestedArray()
=======
    function testGlobalNestedArray()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals(array(array("foo")), $GLOBALS['globalNestedArray']);
    }

<<<<<<< HEAD
    public function testGlobalObject()
=======
    function testGlobalObject()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals((object)array("foo"=>"bar"), $GLOBALS['globalObject']);
    }

<<<<<<< HEAD
    public function testGlobalObjectWithBackSlashString()
=======
    function testGlobalObjectWithBackSlashString()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals((object)array("foo"=>"back\\slash"), $GLOBALS['globalObjectWithBackSlashString']);
    }

<<<<<<< HEAD
    public function testGlobalObjectWithDoubleBackSlashString()
=======
    function testGlobalObjectWithDoubleBackSlashString()
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    {
        $this->assertEquals((object)array("foo"=>"back\\\\slash"), $GLOBALS['globalObjectWithDoubleBackSlashString']);
    }
}
