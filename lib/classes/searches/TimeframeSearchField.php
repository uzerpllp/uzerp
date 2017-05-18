<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * For representing a series of 'timeframes' in which to search.
 * Initial use is for activities with an enddate before today/tomorrow/end of week
 */
class TimeframeSearchField extends SelectSearchField {

	protected $version='$Revision: 1.3 $';
	
	protected $options = array('all'=>'all','today'=>'today','tomorrow'=>'tomorrow','this_week'=>'this_week');
		
	public function toConstraint() {
		switch($this->value) {
			case 'today':
			case 'tomorrow':
				$val = '\''.$this->value.'\'::date';	//this works because Constraint has constants defined for TODAY and TOMORROW, so knows not to escape them.
				break;
			case 'this_week':
				$val = date('Y-m-d',strtotime('next friday'));	//determines the end of the current working-week. Would +7 days be more useful?
				break;
			case 'all': //fallthrough
			default:
				break;
		}
		if(isset($val)) {
			$c = new Constraint($this->fieldname,'<=',$val);
			return $c;
		}
		return false;
	}
}

// end of TimeframeSearchField.php