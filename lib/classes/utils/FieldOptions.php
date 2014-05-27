<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * Used to populate a drop downlist or type ahead field
 * @author de
 */
class FieldOptions {

	protected $version='$Revision: 1.1 $';
	
	/**
	 * The total number of options before limiting
	 * @access private
	 * @var Integer $_count 
	 */
	private $_count;
	
	/**
	 * Array of field names that depend on this field's value
	 * i.e. fields that are constrained by this field
	 * @access private
	 * @var array $_affects 
	 */
	private $_affects;
	
	/**
	 * Array of key-value pairs used to build constraint to get data
	 * @access private
	 * @var array $_depends 
	 */
	private $_depends;
	
	/**
	 * Defines the database field(s) to use for populating the $_data array
	 * @access private
	 * @var string $_identifierfield 
	 */
	private $_identifierfield;
	
	/**
	 * Set true if $_count exceeds autocomplete threshold
	 * @access private
	 * @var Boolean $_autocomplete 
	 */
	private $_autocomplete;
	
	/**
	 * Set true to use the collection to get the data
	 * @access private
	 * @var Boolean $_autocomplete 
	 */
	private $_use_collection=false;
	
	/**
	 * Options data for drop downlist or type ahead field
	 * @access private
	 * @var Array $_data 
	 */
	private $_data=array();
	
	function __construct() {
		
	}

	/**
	 * Accessor for fieldname,value,operator
	 * @param String $var
	 * @magic
	 */
	function __get($var) {
		return $this->$var;
	}

	function __set($key,$val) {
		$this->$key=$val;
	}

}
?>