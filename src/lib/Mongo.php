<?php

	/**
	 *
	 * MMongoClass - For make raw queries to mongodb.
	 * 
	 * To run need mongo driver, intall -> sudo pecl install mongo , config -> php.ini + extension=mongo.so
	 * 
	 *	@version 0.1b
	 * 
	 */
	class MongoClass{

		/**
		 * Current connection to database
		 * @resource connection of PDO
		 */
		private $connection = "";

		/**
		 *	Global configuration of connection to Mongo
		 */
		private $db_config = array(
			"system"	=>	"mongodb",
			"host"		=>	"localhost:27017",
			"db"		=>	"",
			"username"	=>	"",
			"password"	=>	""
			);

		/**
		 *
		 * Set config for object to connect other server, user o data base in first time
		 * 
		 * @param array $config Configuration of database ("system" "host" "user" "password" "bd")
		 *
		 */
		public function __construct( $config = array() ){
			$this->setConfig($config);
			$this->connection = $this->connectDb();
		}

		/**
		 * Private function for check if connection its OK
		 * @return boolean
		 */
		public function isConnected(){
	        return $this->connection->connected;
	    }

		/**
		 *
		 * Set config for object to connect other server, user o data base
		 *
		 * @param array $config Configuration of database ("system" "host" "user" "password" )
		 *
		 */
		public function setConfig( $config = null ){
			if( !empty($config) ){
				$this->db_config = array_merge($this->db_config,$config);
				return true;
			}else{
				return false;
			}
		}

		public function getCollectionNames( $db = null ){
			try {
				if( $this->isConnected() ){
			    	$mongo = $this->connection;
			    }else{
			    	$mongo = $this->connectDB();
			    	$this->connection = $mongo;
			    }
				$db = $mongo->selectDB($db);
				$mongoResult = $db->getCollectionNames();

				if( is_object($mongoResult) ){
					return iterator_to_array($mongoResult);
				}
				return $mongoResult;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}

		/**
		 * Private function make connection to MongoDB
		 * 
		 * @return object|exception Mongo object with connection or message exception of connection fail
		 *
		 */
		private function connectDB(){
			$options = $this->db_config;
			unset($options["system"]);
			unset($options["host"]);
			$options = array_filter($options);
			try {
				$mongo = new MongoClient($this->db_config["system"]."://".$this->db_config["host"],$options);
				return $mongo;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}

		/**
		 * Action for find,update,delete,... data on Mongo
		 *      
		 * @param  string $collection 		Collection to connect
		 * @param  string $action 			Action to do (find,count,insert,drop,...)
		 * @param  array  $options 			Array of options of action
		 * @param  array  $sortParams		Array of options for sort.
		 * @param  string $db 	  			DB To connect   
		 * @return array|string|exception 	Mongo exception, array of results query, string of result query
		 */
		public function query( $collection = null,$action = null,$options = array(),$sortParams = array(),$db = null){
			try {
				if( $this->isConnected() ){
			    	$mongo = $this->connection;
			    }else{
			    	$mongo = $this->connectDB();
			    	$this->connection = $mongo;
			    }
			    if( empty($db) ){
			    	$db = $this->db_config["db"];
			    }
				$db = $mongo->selectDB($db);
				$mongoCollection = new MongoCollection($db, $collection);
				$mongoResult = call_user_func_array(array($mongoCollection, $action), $options);

				//$mongoResult = $mongoCollection->$action($options[0],$options[1]);
				if( is_object($mongoResult) ){
					if(!empty($sortParams)){
						$mongoResult = $mongoResult->sort($sortParams);
					}
					return iterator_to_array($mongoResult);
				}
				return $mongoResult;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}

	}

?>