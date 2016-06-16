<?php
	/**
	  * @author Jesús Benages Sales jobinary@hotmail.com
	  * @license https://opensource.org/licenses/MIT MIT
	  */

	/**
	 * UserAlert - Alerts relating to users.
	 */
	class UserAlert implements Alert
	{
		/**
		 * @var int 	First hour of schedule that user is working.
		 */
		private $firstHour;

		/**
		 * @var int 	Last hour of schedule that user is working.
		 */
		private $lastHour;

		/**
		 * @var String[] 	Days of holydais that workers not work.
		 */
		private $holydays = array();

		/**
		 * @var String[] 	Types of alerts to send mail admin.
		 */
		private $typeAlertMail = array(	
			"user_weekend",
			"user_holyday",
			"user_offhour"
			);

		/**
		 * @param int  		$firstHour 	The first hour schedule work.
		 * @param int  		$firstlast 	The last hour schedule work.
		 * @param String[] 	$holydays 	The holydays of workers.
		 */
		function __construct($firstHour,$lastHour,$holydays){
			$this->firstHour = $firstHour;
			$this->lastHour = $lastHour;
			$this->holydays = $holydays;
		}

		/**
		 * @param  int 	$time 	The timestamp.
		 * @return bool 		True if is holyday false if isn't.
		 */
		private function isHolyday( $time ){
			$day = date("j",$time);
			$month = date("n",$time);
			return in_array($day."/".$month,$this->holydays);
		}

		/**
		 * @param  int 	$time 	The timestamp.
		 * @return bool 		True if is weekend false if isn't. 
		 */
		private function isWeekend( $time ){
			$weekend = array(6,7);
			$dayWeek = date("N",$time);
			return in_array($dayWeek,$weekend);
		}

		/**
		 * @param  int 	$time 	The timestamp.
		 * @return bool 		True if is off hours the worker false if isn't. 
		 */
	 	private function isOffHours( $time ){

	 		$offHours = true;

	 		$minHour = $this->firstHour;
			$maxHour = $this->lastHour;

	 		$hourNow = date("H",$time);
			if( $hourNow == "00" ){
				$hourNow = "24";
			}

	 		if( $minHour < $maxHour ){
				if( $minHour <= $hourNow && $maxHour >= $hourNow){
					$offHours = false;
				}
			}else{
				if( $minHour <= $hourNow || $maxHour >= $hourNow ){
					$offHours = false;
				}
			}

			return $offHours;

	 	}

	 	/**
	 	 * @param string 	$user 			The user name of worker.
	 	 * @param String[] 	$currentUsers 	All users known.
	 	 * 
	 	 * @return bool 	True if is unknown user or false if is known user.
	 	 */
	 	private function isUnknownUser( $user = null ,$currentUsers = null){
	 		if( empty($user) ){
	 			throw new Exception("Empty user.");
	 		}
	 		return !in_array($user,$currentUsers);
	 	}

	 	/**
	 	 * @param string 	$typeAlert 	Type of alert.
	 	 * 
	 	 * @return bool 	True if is type of alert to send email or False if not.
	 	 */
		public function possibleMail($typeAlert){
			return in_array($typeAlert,$this->typeAlertMail);
		}

	 	/**
		 * @param int 		$time 			Date of message created.
		 * @param string 	$message 		Message of log that may be a alert.
		 * @param string 	$user 			The name of user that make the message.
		 * @param String[]	$currentUsers 	All users known.
		 * @param string 	$typeAlert 		Write the type of alert.
		 * 
		 * @return bool 	True if is alert or false if isn't.
		 */
		public function isAlert( $time, $message,$user = null,$currentUsers = null,&$typeAlert = null ){
			
			if( empty($time) ){
	 			throw new Exception("Empty time.");
	 		}

	 		$alert = false;

			try{
				if( $this->isUnknownUser($user,$currentUsers) ){
					$typeAlert = "user_unknown";
					$alert = true;
				}elseif( $this->isWeekend($time) ){
					$typeAlert = "user_weekend";
					$alert = true;
				}elseif( $this->isHolyday($time) ){
					$typeAlert = "user_holyday";
					$alert = true;
				}elseif( $this->isOffHours($time) ){
					$typeAlert = "user_offhour";
					$alert = true;
				}
			}catch( Exception $e ){
				throw new Exception( $e->getMessage() );
			}
			return $alert;

		}

	}