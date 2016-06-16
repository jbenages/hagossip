<?php
 
    require_once(realpath(dirname(__FILE__))."/../class/abstract/Alert.php");
    require_once(realpath(dirname(__FILE__))."/../class/UserAlert.php");
    class UserAlertTest extends PHPUnit_Framework_TestCase
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

            $this->config = parse_ini_file(realpath(dirname(__FILE__))."/../../config/example.app.ini",true);

            $Object = new UserAlert(
                $this->config["schedule"]["firsthour"],
                $this->config["schedule"]["lasthour"],
                $this->config["schedule"]["holydays"]
                );
            return $Object;
        }

        function testExistsClass()
        {
            $this->assertTrue(class_exists("UserAlert"));
        }


        function testIsAlertWithEmptyTimeParamReturnsException()
        {
            try{
                $typeAlert = null;
                $this->invokeMethod($this->instanceObject(),"isAlert",array(null,"mensaje","root",array("root"),&$typeAlert));
            }catch(Exception $e){
                $this->assertEquals("Empty time.",$e->getMessage());
            }
        }

        function testIsAlertWithEmptyUserParamReturnsException()
        {
            try{
                $typeAlert = null;
                $this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("2015-12-28 12:00:00"),"mensaje",null,array("root"),&$typeAlert));
            }catch(Exception $e){
                $this->assertEquals("Empty user.",$e->getMessage());
            }
        }

        function testIsAlertWithRightParamReturnsFalse()
        {
            $typeAlert = null;
            $this->assertFalse($this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("2015-12-28 12:00:00"),"mensaje","root",array("root"),&$typeAlert)));
        }

        function testIsAlertWithWeekendDateParamReturnsTrue()
        {
            $typeAlert = null;
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("next Sunday"),"mensaje","root",array("root"),&$typeAlert)));
        }

        function testIsAlertWithHolydayDateParamReturnsTrue()
        {
            $typeAlert = null;
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("2015-01-01 12:00:00"),"mensaje","root",array("root"),&$typeAlert)));
        }

        function testIsAlertWithRightParamReturnsTrue()
        {
            $typeAlert = null;
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("2015-12-28 02:00:00"),"mensaje","root",array("root"),&$typeAlert)));
        }

        function testIsAlertWithUnknownUserParamReturnsTrue()
        {
            $typeAlert = null;
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"isAlert",array(strtotime("2015-12-28 12:00:00"),"mensaje","pepe",array("root"),&$typeAlert)));
        }

        function testPossibleMailWithKnowTypeParamReturnsTrue()
        {
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"possibleMail",array("user_weekend")));
        }

        function testPossibleMailWithUnknowTypeParamReturnsFalse()
        {
            $this->assertFalse($this->invokeMethod($this->instanceObject(),"possibleMail",array("user_barracuda")));
        }

        function testIsWeekendWithWeekenddateParamReturnsTrue()
        {
            $typeAlert = null;
            $this->assertTrue($this->invokeMethod($this->instanceObject(),"isWeekend",array(1456652851)));
        }


    }