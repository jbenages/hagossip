<?php
	/**
	  * @author Jesús Benages Sales jobinary@hotmail.com
	  * @license https://opensource.org/licenses/MIT MIT
	  */

	/**
	 * LineLog - Line of syslog.
	 */
	abstract class LineLog
	{
		/**
		 * @var String[] With fields of line with data of log.
		 */
		protected $lineFields = array(
			"_id" 		=>	"",
			"pid" 		=>	"",
			"message" 	=>	"",
			"priority" 	=>	"",
			"isodate" 	=>	"",
			"dateInt" 	=>	"",
			"seqnum" 	=>	"",
			"program" 	=>	"",
			"time" 		=>	"",
			"server" 	=>	"",
			"type"		=>	""
			);

		/**
		 * @var String[] With all types of line log.
		 */
		protected $typesLine = array();

		/**
		 * @var String[] With all types of lines log that may be a alert.
		 */
		protected $possibleAlert = array();

		/**
		 * Get a field of linelog.
		 * 
		 * @param string $field Name of field to get.
		 * 
		 * @return string A field of selection
		 */
		public function getField($field){
			return $this->lineFields[$field];
		}

		/**
		 * @param String[] $lineFields of fields.
		 */
		public function setAllFields($lineFields){
			$this->lineFields = $lineFields;
		}

		/**
		 * @return String[] of fields of log.
		 */
		public function getAllFields(){
			return $this->lineFields;
		}

		/**
		 * @param string 	$field	Name of field to set.
		 * @param string 	$date 	Data to set field.
		 */
		public function setField($field,$data){
			$this->lineFields[$field] = $data;
		}

		/**
		 * @param string 	$field 	Delete field of array lineFields.
		 */
		public function deleteField($field){
			unset($this->lineFields[$field]);
		}

		/**
		 * Get tipe of log line.
		 */
		abstract function getType();

		/**
		 * Set fields of all type of system. (Like SSH)
		 */
		abstract function setCustomFields();

	}