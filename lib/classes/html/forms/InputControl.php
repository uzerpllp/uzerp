<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
abstract class InputControl extends FormControl {
	public $name, $type;
	function __construct(DataField $field) {
		$this->_data=$field;
		$this->extractData();
	}
	#[\Override]
	function render($additional='') {
		$html="{input type='{$this->type}'  attribute='{$this->name}' {$this->getClassNameString()}}\n";
		return $html;
	}

}
?>
