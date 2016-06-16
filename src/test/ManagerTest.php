<?php

    require_once(realpath(dirname(__FILE__))."/../lib/Mongo.php");
    require_once(realpath(dirname(__FILE__))."/../class/abstract/LineLog.php");
    require_once(realpath(dirname(__FILE__))."/../class/abstract/Alert.php");
    require_once(realpath(dirname(__FILE__))."/../class/SshLog.php");
    require_once(realpath(dirname(__FILE__))."/../class/UserAlert.php");
    require_once(realpath(dirname(__FILE__))."/../class/Manager.php");

    class ManagerTest extends PHPUnit_Framework_TestCase
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
            $Object = new Manager();
            return $Object;
        }

        function getMongoConnection(){
            return new MongoClass(array( "db" => "syslog" ));
        }

        function testExistsClass()
        {
            $this->assertTrue(class_exists("Manager"));
        }

        function testGetLinesSyslogReturnsArray()
        {
            $this->assertInternalType("array",$this->invokeMethod($this->instanceObject(),"getLinesSyslog",array($this->getMongoConnection(),"ssh")));
        }

        function testGetUsersReturnsArray()
        {
            $this->assertInternalType("array",$this->invokeMethod($this->instanceObject(),"getUsers",array()));
        }

        function testExistsSystemWithEmtyParamsReturnsException()
        {
            try{
                $this->invokeMethod($this->instanceObject(),"existsSystem",array(null));
            }catch(Exception $e){
                $this->assertEquals("Empty system.",$e->getMessage());
            }
        }

        /*function testReorderDataReturnsArray()
        {
            $this->assertInternalType("array",$this->invokeMethod($this->instanceObject(),"reorderData",array($this->getMongoConnection())));
        }*/

    }