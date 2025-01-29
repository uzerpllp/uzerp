<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DateValidator implements FieldValidation {

	protected $version='$Revision: 1.3 $';
	
	private $message_stub='%s is not a valid date.';
	
	/**
	 * Validates a date
	 *
	 * @param	string	value passed from form
	 * @return	boolean	false on fail, otherwise returns a yyyy-mm-dd date string
	 * @todo	Always allow a date in ISO 8601 (yyyy-mm-dd) format
	 */
	function test(DataField $field, Array &$errors=array()) {

		$format = DATE_FORMAT;

		if(function_exists('date_parse_from_format')) {
			if(date_parse_from_format(DATE_FORMAT, $field->value)!==false) {
				return $field->value;
			}
			if(date_parse_from_format(DATE_TIME_FORMAT, $field->value)!==false) {
				return $field->value;
			}
		}

		//split date into component parts
		$f_val = $field->finalvalue;
		$bits = preg_split('#[^0-9]#',$f_val);
		if(empty($f_val)) {
			return 'null';
		}

		if(count($bits)!==3 && count($bits)!==5) {			//wrong number of parts
			$message=sprintf($this->message_stub,$field->tag);
			$errors[$field->name]=$message;

			return false;
		}

		$year = $bits[2];			//set 4 digit year value
		if($year<100)
			$year+=($year>70)?1900:2000;	//anything after 70 is 20th century


		if($format=="d/m/y" || $format=="d/m/Y") { //European
			$month = $bits[1];
			$day = $bits[0];
		}

		elseif($format=="m/d/y" || $format=="m/d/Y") { //US
			$month = $bits[0];
			$day = $bits[1];
		}


		//check for valid date
		if(!checkdate($month, $day, $year)
		 || strlen($year)==3 || strlen($year)>4 ) {
			$message=sprintf($this->message_stub,$field->tag);
			$errors[$field->name]=$message;
			return false;
		}

		if (count($bits) == 5) {
			$return = "$year-$month-$day {$bits[3]}:{$bits[4]}:00";
			return $return;
		}
		return $year . "-" . $month . "-" . $day; //return date in yyyy-mm-dd format

	}

}

?>
