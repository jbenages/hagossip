<?php
	/**
	  * @author Jesús Benages Sales jobinary@hotmail.com
	  * @license https://opensource.org/licenses/MIT MIT
	  */

	/**
	*  	SshLog - Log lines of SSH service.
	*
	*  	Type of messages:
	*	Received disconnect from 1.1.1.1: 11: disconnected by user
	*	Accepted publickey for root from 1.1.1.1 port 41615 ssh2
	*	pam_unix(sshd:session): session opened for user root by (uid=0)
	*	pam_unix(sshd:session): session closed for user root
	*	Did not receive identification string from 1.1.1.1
	*	Invalid user henriq from 1.1.1.1
	*	pam_unix(sshd:auth): check pass; user unknown
	*	pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=www.test.com
	*	Accepted password for fred from 1.1.1.1 port 6647 ssh2
	*	error: Authentication key RSA SHA256:jXEPmu4thnubqPUDDKDs31MOVLQJH6FfF1XSGT748jQ revoked by file /etc/ssh/ssh_revoked_keys
	*	input_userauth_request: invalid user simscan [preauth]
	*	Failed password for root from 1.1.1.1 port 64125 ssh2
	*   Failed password for invalid user henriq from 1.1.1.1 port 37647 ssh2
	*	Address 1.1.1.1 maps to redesmayab.com, but this does not map back to the address - POSSIBLE BREAK-IN ATTEMPT!
	* 	Connection closed by 1.1.1.1 [preauth]
	*
	 */
	class SshLog extends LineLog
	{
		/**
		 * @param String[] 	$lineFields		Array of field for each line.
		 */
		function __construct( $lineFields = null ){

			$this->typesLine = array(
				"disconnect" 				=> 	"/Received disconnect from/",
				"accept_publickey"			=>	"/Accepted publickey /",
				"session_opened"			=>	"/session opened for/",
				"session_closed"			=>	"/session closed for/",
				"not_idetification" 		=>	"/Did not receive identification string from /",
				"invalid_user"				=>	"/Invalid user/",
				"check_pass"				=>	"/pam_unix\(sshd\:auth\)\: check pass/",
				"authentication_failure"	=>	"/pam_unix\(sshd\:auth\)\: authentication failure\;/",
				"accepted_password"			=>	"/Accepted password for/",
				"failed_password"			=>	"/Failed password for/",
				"error_key"					=>	"/error\: Authentication key RSA/",
				"invalid_user_preauth"		=>	"/input_userauth_request\: invalid user/",
				"breakin_attempt"			=>	"/but this does not map back to the address \- POSSIBLE BREAK\-IN ATTEMPT\!/",
				"connection_closed"			=>	"/Connection closed by /"
			);

			$this->possibleAlert = array(
				"user_unknown",
				"accept_publickey",
				"accepted_password"
				);

			if( !empty($lineFields) ){
				$this->setAllFields($lineFields);
			}
		}

		/**
		 * @param string $parser 	Regexp to execute with message string.
		 * @param string $message 	The message stored in line of log.
		 * 
		 * @return String[] 	With all results of parse.
		 */
		private function getParse($parser = null, $message = null ){

			if( empty($parser) ){
				throw new Exception("Empty parser.");
			}

			if( empty($message) ){
				throw new Exception("Empty message.");
			}

			preg_match_all($parser,$message,$parse);
			return $parse;

		}

		/**
		 * Get the ip of line of log.
		 * 
		 * @return string|null 	Ip of line log or, if not find, null.
		 */
		public function getIp(){
			try{
				$ip = $this->getParse("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/",$this->lineFields["message"]);
				$this->lineFields["ip"] = @$ip[0][0];
			}catch(Exception $e){
				throw new Exception($e->getMessage());
			}
			return $this->lineFields["ip"];
		}

		/**
		 * Get the user of line of log.
		 * 
		 * @return string|null 	User name of line of log or, if not find, null.
		 */
		public function getUser(){
			try{
				$user = $this->getParse("/(for user|for invalid user|for|user) ([A-Za-z][A-Za-z0-9_]*)/",$this->lineFields["message"]);
				$this->lineFields["user"] = @$user[2][0];
			}catch(Exception $e){
				throw new Exception($e->getMessage());
			}
			return $this->lineFields["user"];
		}



		/**
		 * Get type of message log.
		 * 
		 * @return string|null 	Type of message or null if not find it.
		 */
		public function getType(){

			$return = null;

			foreach( $this->typesLine as $typeLine => $parse ){
				if( preg_match( $this->typesLine[$typeLine],$this->lineFields["message"]) ){
					$return = $typeLine;
					$this->lineFields["type"] = $typeLine;
				}
			}
			return $return;

		}

		/**
		 * Store the custom fields of line log.
		 */
		public function setCustomFields(){
			try{
				$this->getUser();
				$this->getIp();
				$this->getType();
			}catch(Exception $e){
				throw new Exception($e->getMessage());
			}	
		}

		/**
		 * Check if the type of line log is possible alert.
		 * 
		 * @return bool 	True if is possible or False if not.
		 */
		public function possibleAlert(){
			return in_array($this->getType(),$this->possibleAlert);
		}

	}