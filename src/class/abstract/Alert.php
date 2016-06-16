<?php
	/**
	  * @author Jesús Benages Sales jobinary@hotmail.com
	  * @license https://opensource.org/licenses/MIT MIT
	  */

	/**
	 * Alert - Interface for types of alert.
	 */
	interface Alert
	{	
		/**
		 * @param int 		$time 		Date of message created.
		 * @param string 	$message 	Message of log that may be a alert.
		 */
		public function isAlert($time,$message);
	}