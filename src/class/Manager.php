<?php

	/**
	  * @author Jesús Benages Sales jobinary@hotmail.com
	  * @license https://opensource.org/licenses/MIT MIT
	  */

	/**
	 * 	Manager - Main class
	 * 
	 * 	This class manage all Data class, Alert class and Mongo driver for search the alert in data syslog and write documents in Mongo.
	 * 
	 */
	class Manager
	{

		/**
		 * @var array	Contains global configuration set by user.
		 */
		private $config;

		/**
		 * Charge global configuration in config var;
		 */
		function __construct(){
			$this->config = parse_ini_file(realpath(dirname(__FILE__))."/../../config/app.ini",true);
		}

		/**
		 * Send mail to admin with line of data-alert.
		 */
		private function sendMail($system,$email,$lineAlert){
			mail($email,"Alert System:".$system." -Date:".date("Y-m-d H:i:s")," Data:".json_encode($lineAlert));
		}

		/**
		 * Extract lines writes by syslog in mongodb for each type of system (like ssh).
		 *
		 * @uses MongoClass::query()	to do a query in db system.
		 * @uses SshLog
		 * 
		 * @param object $mongo		Mongodb object to make queries.
		 * @param string $system 	Name of system for extract lines.
		 * 
		 * @return String[] Returns array of lines in mongo.
		 */
		private function getLinesSyslog($mongo, $system = null ){

			$linesSystem = array();
			if( !empty($system) ){
				$resultQuery = $mongo->query($system,"find",array());
				foreach( $resultQuery as $id => $values ){
					$linesSystem[] = new SshLog($resultQuery[$id]); 
				}
			}
			return $linesSystem;

		}

		/**
		 * To extract users for use in alerts.
		 *
		 * @return String[] Returns array with users.
		 */
		private function getUsers(){
	 		return $this->config["alerts"]["user"];
		}

		/**
		 * To check if system exists.
		 * 
		 * @param string $system Name of system to check.
		 * 
		 * @throws Exception if System name is empty or not exists in array of systems.
		 */
		private function existsSystem( $system ){

			if( empty($system) ){
				throw new Exception("Empty system.");
			}

			$systems = array("ssh");
			if( !in_array($system,$systems) ){
				throw new Exception("No system available: ".$system);
			}

		}

		/**
		 * Store the lines of alert in db for a system.
		 * 
		 * @param string 	$system 	Name of system.
		 * @param object 	$mongo		Mongodb object to make queries.
		 * @param String[]  $linesAlert Array with object of system Data class.
		 * 
		 * @throws Exception if not save correctly a line of data in mongo.
		 */
		private function saveAlert( $system,$mongo,$linesAlert ){

			$iTop = sizeof($linesAlert);

			for( $i = 0; $i < $iTop; $i++ ){
				try{
					$this->insertData($mongo,$system."Alert",array( $linesAlert[$i]->getAllFields()));
				}catch(Exception $e){
					throw new Exception($e->getMessage());
				}
			}

		}

		/**
		 * Search alert in lines of syslog of a system.
		 * 
		 * @uses UserAlert
		 * 
		 * @param string 	$system 		Name of system.
		 * @param object 	$mongo			Mongodb object to make queries.
		 * @param String[]  $linesSyslog	Array with lines of syslog store in mongodb.
		 * 
		 * @throws Exception if fail search alert in each line.
		 * 
		 * @return String[] With alerts lines to store on mongodb.
		 * 
		 */
		private function searchAlerts($system,$mongo,$linesSyslog){

			$userAlert = new UserAlert(
				$this->config["schedule"]["firsthour"],
				$this->config["schedule"]["lasthour"],
				$this->config["schedule"]["holydays"]
				);

			$currentUsers = $this->getUsers($mongo);

			$dataAlert = array();
			$typeAlert = "";
			

			foreach( $linesSyslog as $id => $values ){
				try{

					$linesSyslog[$id]->setCustomParams();
					
					$user = $linesSyslog[$id]->getField("user");
					if( !empty($user) && $data[$id]->possibleAlert() ){

						if( $userAlert->isAlert(
							$linesSyslog[$id]->getField("time"),
							$linesSyslog[$id]->getField("message"),
							$linesSyslog[$id]->getField("user"),
							$currentUsers,
							$typeAlert
							)
							){

							$linesSyslog[$id]->setField("typeAlert",$typeAlert);
							$dataAlert[] = $linesSyslog[$id];

							if( $userAlert->possibleMail($typeAlert) ){
								$this->sendMail($system,$this->config["alerts"]["email"],$linesSyslog[$id]->getAllFields());
							}

						}

					}

				}catch(Exception $e){
					echo date("Y-m-d H:i:s")."error: ".$e->getMessage();
				}
			}

			try{
				$this->saveAlert( $system,$mongo,$dataAlert );
			}catch(Exception $e){
				echo date("Y-m-d H:i:s")." error: ".$e->getMessage();
			}

			return $dataAlert;

		}

		/**
		 * Delete document from collection with raw lines of syslog in mongodb.
		 * 
		 * @param object 	$mongo		Mongodb object to make queries.
		 * @param string 	$collection	Collection for store data in mongo.
		 * @param String[] 	$data		Data to remove of collection.
		 * 
		 */
		private function deleteData($mongo,$collection,$document){
			try{
				$ids = array();
				$iTop = sizeof($document);
				for($i = 0; $i < $iTop ; $i++ ){
					$mongo->query($collection,"remove",array(array("_id"=>$document[$i]->getField("_id")),array('w'=>1)));
				}
			}catch(Exception $e){
				echo date("Y-m-d H:i:s")." error: ".$e->getMessage() ;
			}

		}

		/**
		 * Insert in collection the data of syslog in mongodb.
		 * 
		 * @param object 	$mongo		Mongodb object to make queries.
		 * @param string 	$collection	Collection for store data in mongo.
		 * @param String[] 	$data		Data to store in collection.
		 * 
		 * @throws Exception if fail insert in mongodb.
		 */
		private function insertData($mongo,$collection,$data){
			try{
				$mongo->query($collection,"batchInsert",array($data));
			}catch(Exception $e){
				throw new Exception($e->getMessage());
			}
		}

		/**
		 * Move the lines of syslog in mongodb to order collections with dates. Like this: ssh201601
		 * And delete lines with erros in setCustomParams.
		 * 
		 * @param object 	$mongo			Mongodb object to make queries.
		 * @param string 	$collection		Collection for store data in mongo.
		 * @param String[] 	$linesSyslog 	Data to store in collection.
		 * 
		 * @return String[] of Syslog lines with custom data seted.
		 */
		private function reorderData($mongo,$system, $linesSyslog ){

			$iTop = sizeof($linesSyslog);

			$newData = array();

			for( $i = 0 ;$i < $iTop; $i++ ){
				try{
					$linesSyslog[$i]->setCustomFields();
					$newData[$linesSyslog[$i]->getField("dateInt")][] = $linesSyslog[$i]->getAllFields();
				}catch(Exception $e){
					unset($linesSyslog[$i]);
					echo date("Y-m-d H:i:s")."id:".$linesSyslog[$i]->getField("_id")->{'$id'}." error:".$e->getMessage();
				}
			}

			foreach( $newData as $date => $data ){
				try{
					$this->insertData($mongo,$system.$date,$newData[$date]);
				}catch(Exception $e){
					echo $e->getMessage();
				}
			}

			return $linesSyslog;

		}

		/**
		 * Executable function for run program.
		 */
		public function main(){

			$mongo = new MongoClass($this->config["database"]);
			$data = $this->getLinesSyslog( $mongo,"ssh" );
			$dataAlert = $this->searchAlerts("ssh",$mongo,$data);
			$dataReorder = $this->reorderData($mongo,"ssh",$data);
			$this->deleteData($mongo,"ssh",$data);
		}

	}
