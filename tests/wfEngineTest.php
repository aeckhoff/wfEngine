<?php

require_once dirname(__FILE__).'/../wfEngine.php';
require_once dirname(__FILE__).'/wfEngineMock.php';
require_once dirname(__FILE__).'/wfSessionMock.php';

class wfEngineTest extends PHPUnit_Framework_TestCase {

    protected $object;
    protected $mockObject;
    protected $sessionObject;

    protected function setUp() {
        $this->mockObject = new wfEngineMock();
        $this->sessionObject = new wfSessionMock();
        $this->object = new wfEngine();
        $this->object->setSessionObject($this->sessionObject);
        $this->object->setPrefix('wf_');
    }

    protected function tearDown() {
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testFailingSetWFObject() {
        $this->object->setWFObject('throws error');
    }

    public function testSetWFObject() {
        $this->object->setWFObject($this->mockObject);
        $this->assertEquals( $this->mockObject, $this->object->getWFObject() );
    }

    public function testSetDefaultCommand() {
        $this->object->setDefaultCommand("test");
        $this->assertEquals( "test", $this->object->getDefaultCommand() );

        try {
            $this->assertEquals( $this->mockObject, $this->object->getDefaultCommand() );
        }
        catch (Exception $expected) {
            return;
        }
        $this->fail('An expected Exception has not been raised.');
    }

    public function testSetMustReload() {
        $this->object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($this->object);
        $this->object->setDefaultCommand('mustreload');
	$this->object->executeWF(false);
        $this->assertTrue( $this->object->mustReload() );
    }

    public function testCommandWasCalledExternal() {
        $this->object->setWFObject($this->mockObject);
        $this->object->setDefaultCommand('start');
	$this->object->executeWF(false);
        $this->assertTrue( $this->object->commandWasCalledExternal() );
    }

    public function testCommandWasNotCalledExternal() {
        $this->object->setWFObject($this->mockObject);
        $this->object->setDefaultCommand('start');
	$this->object->executeWF('callstart');
        $this->assertFalse( $this->object->commandWasCalledExternal(false) );
    }

    public function testCommandWasNotCalledMustReload() {
        $this->object->setWFObject($this->mockObject);
        $this->object->setDefaultCommand('start');
	$this->object->executeWF('callstart');
        $this->object->setCallDefaultOnError(true);
        $this->assertEquals( 'reload start', $this->object->commandWasCalledExternal(true) );
    }

    public function testsetMaxInternalCalls() {
        $this->object->setMaxInternalCalls(15);
        $this->assertEquals( 15, $this->object->getMaxInternalCalls() );
    }

    public function testSetPrefix() {
        $this->object->setPrefix("test_");
        $this->assertEquals( "test_", $this->object->getPrefix() );
    }

    public function testSetPostfix() {
        $this->object->setPostfix("test_");
        $this->assertEquals( "test_", $this->object->getPostfix() );

    }

    public function testExecuteWF() {
        $this->object->setWFObject($this->mockObject);
	$this->object->setDefaultCommand('start');
	$this->object->executeWF(false);
        $this->assertEquals( "start", $this->mockObject->output );
    }

    public function testExecuteSeriesWF() {
        $this->object->setWFObject($this->mockObject);
	$this->object->setDefaultCommand('callstart');
	$this->object->executeWF(false);
        $this->assertEquals( "start", $this->mockObject->output );
    }

    public function testCheckLast() {
        $this->object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($this->object);
	$this->object->setDefaultCommand('callchecktest');
	$this->object->executeWF(false);
        $this->assertEquals( "last was callchecktest", $this->mockObject->output );
    }

    public function testCheckMultipleLast() {
        $this->object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($this->object);
	$this->object->setDefaultCommand('callmultiplechecktest');
	$this->object->executeWF(false);
        $this->assertEquals( "last was callmultiplechecktest or wasnotcalled", $this->mockObject->output );

	$this->object->executeWF('callmultiplechecktestagain');
        $this->assertEquals( "last was callmultiplechecktest or wasnotcalled", $this->mockObject->output );
    }

    public function testHashTag() {
        $this->object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($this->object);
        $hash = $this->object->getHashTag();
        $this->object->setGivenHashTag($hash);
	$this->object->setDefaultCommand('start');
        $this->assertTrue( $this->object->checkGivenHash());
    }

    public function testInvalidHashTag() {
        $this->object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($this->object);
        $this->object->setGivenHashTag('invalidhash');
	$this->object->setDefaultCommand('start');
        $this->assertFalse( $this->object->checkGivenHash());
    }

    public function testCheckIfCalledViaGET() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $object = new wfEngine();
        $object->setSessionObject($this->sessionObject);
        $object->setPrefix('wf_');
        $object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($object);
	$object->setDefaultCommand('start');
        $this->assertTrue($object->checkIfCalledViaGET());
        $this->assertFalse($object->checkIfCalledViaPOST());
        $this->assertFalse($object->checkIfCalledViaAJAX());
    }

    public function testCheckIfCalledViaPOST() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $object = new wfEngine();
        $object->setSessionObject($this->sessionObject);
        $object->setPrefix('wf_');
        $object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($object);
	$object->setDefaultCommand('start');
        $this->assertFalse($object->checkIfCalledViaGET());
        $this->assertTrue($object->checkIfCalledViaPOST());
        $this->assertFalse($object->checkIfCalledViaAJAX());
    }

    public function testCheckIfCalledViaAJAX() {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $object = new wfEngine();
        $object->setSessionObject($this->sessionObject);
        $object->setPrefix('wf_');
        $object->setWFObject($this->mockObject);
        $this->mockObject->setWFObject($object);
	$object->setDefaultCommand('start');
        $this->assertFalse($object->checkIfCalledViaGET());
        $this->assertFalse($object->checkIfCalledViaPOST());
        $this->assertTrue($object->checkIfCalledViaAJAX());
    }

}