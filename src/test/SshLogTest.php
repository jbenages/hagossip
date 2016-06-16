<?php

    require_once(realpath(dirname(__FILE__))."/../class/abstract/LineLog.php");
    require_once(realpath(dirname(__FILE__))."/../class/SshLog.php");
    class SshLogTest extends PHPUnit_Framework_TestCase
    {

        /**
         * Call protected/private method of a class.
         *
         * @param object &$object    Instantiated object that we will run method on.
         * @param string $methodName Method name to call
         * @param array  $parameters Array of parameters to pass into method.
         *
         * @return mixed Method return.
         */
        function invokeMethod(&$object, $methodName, array $parameters = array())
        {
            $reflection = new \ReflectionClass(get_class($object));
            $method = $reflection->getMethod($methodName);
            $method->setAccessible(true);

            return $method->invokeArgs($object, $parameters);
        }

        function instanceObject()
        {
            $Object = new SshLog(
                array(
                    "message"   => "Accepted password for henriq from 1.1.1.1 port 6647 ssh2",
                    )
                );
            return $Object;
        }

        function testExistsClass()
        {
            $this->assertTrue(class_exists("SshLog"));
        }

        function testGetParseWithEmptyMessageParamsReturnsException()
        {
            try{
                $this->invokeMethod($this->instanceObject(),"getParse",array("pppp",null));
            }catch(Exception $e){
                $this->assertEquals("Empty message.",$e->getMessage());
            }
        }

        function testGetParseWithEmptyParamReturnsException()
        {
            try{
                $this->invokeMethod($this->instanceObject(),"getParse",array(null,null));
            }catch(Exception $e){
                $this->assertEquals("Empty parser.",$e->getMessage());
            }
        }

        function testGetParseWithIpRightParamsReturnsString()
        {
            $this->assertEquals("1.1.1.1",$this->instanceObject()->getIp());
        }

        function testGetParseWithUserRightParamsReturnsString()
        {
            $this->assertEquals("henriq",$this->instanceObject()->getUser());
        }

        function testGetTypeWithRightParamsReturnsString()
        {
            $this->assertEquals("accepted_password",$this->instanceObject()->getType());
        }

        function testPossibleAlertKnowTypeParamReturnsTrue()
        {

            $sshlog = $this->instanceObject();
            $sshlog->setField("type","failed_password");

            $this->assertTrue($sshlog->possibleAlert());
        }

        function testPossibleAlertWithUnknowTypeParamReturnsFalse()
        {

            $sshlog = $this->instanceObject();
            $sshlog->setField("type","invalid_duck");

            $this->assertTrue($sshlog->possibleAlert());
        }

    }