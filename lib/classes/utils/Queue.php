<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Queue implements Countable {

	protected $version='$Revision: 1.2 $';
	
	private $length;
	private $data=array();
	function __construct($length=10) {
		$this->length=$length;		
		$this->data=array();
	}
	
	/**
	 * Adds a new element to the front of the array, losing the oldest one
	 */
	function push($element) {
		if(false!==($key=array_search($element,$this->data))) {
			unset($this->data[$key]);
		}
		array_unshift($this->data,$element);
		$this->data=array_slice($this->data,0,$this->length);
	}
	
	function remove($element) {
		if(false!==($key=array_search($element,$this->data))) {
			unset($this->data[$key]);
		}
	}
	
	function pop() {
		$return=$this->data[count($this->data)-1];
		unset($this->data[count($this->data)-1]);
		return $return;
	}
	
	function toArray() {
		return $this->data;
	}
	function clear() {
		$this->data=array();
	}
	
	/**
	 * To implement Countable
	 */
	function count() {
		return count($this->data);	
	}
	
}


?>