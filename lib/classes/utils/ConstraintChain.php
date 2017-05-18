<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ConstraintChain implements Iterator{

	protected $version='$Revision: 1.6 $';
	
	private $_pointer=0;
	private $_constraints=array();

	function __construct() {
	}

	function add($c, $type = 'AND', $negate = FALSE) {
		if (!$type == 'AND' && !$type=='OR') {
			$type = 'AND';
		}
		
		if (!is_bool($negate)) {
			$negate = FALSE;
		}
		
		if($c instanceof ConstraintChain && count($c->contents)==0) {
			return $this;
		}
//		$this->removeByField($c);
		if ($this->find($c)) {
			return $this;
		}
		else {
			$this->_constraints[]=array('constraint'=>$c,'type'=>$type,'negate'=>$negate);
		}
		return $this;
	}

	function find($c) {
		if($c instanceof ConstraintChain) {
			foreach($c as $constraint) {
				$return =  $this->find($constraint['constraint']);
				if ($return) {
					return $return;
				}
			}
		}
		foreach ($this->_constraints as $constraint) {
			if ($constraint['constraint'] instanceof ConstraintChain) {
				$return = $constraint['constraint']->find($c);
				if ($return) {
					return $return;
				}
			}
			else {
				if ($c == $constraint['constraint']) {
					return true;
				}
			}
		}
		return false;
	}
	
	function removeLast() {
		return array_pop($this->_constraints);
	}

	function removeByField($fieldname) {
		if ($fieldname instanceof ConstraintChain) {
			foreach ($fieldname as $constraint)	{
				$this->removeByField($constraint['constraint']);
			}
		}
		if ($fieldname instanceof Constraint) {
			$this->removeByField($fieldname->fieldname);
		}
		foreach ($this->_constraints as $key=>$constraint) {
			if ($constraint['constraint'] instanceof ConstraintChain) {
				$constraint['constraint']->removeByField($fieldname);
				if (empty($constraint['constraint']->_constraints)) {
					unset($this->_constraints[$key]);
				}
			} else {
				if ($constraint['constraint']->fieldname==$fieldname) {
					unset($this->_constraints[$key]);
				}
			}
		}
		$constraints=array_values($this->_constraints);
		$this->_constraints = $constraints;
	}
	
	function __get($var) {
		if($var=='contents')
			return $this->_constraints;
	}

	function __toString() {
		if(func_num_args()>0) {
			$table_prefix=func_get_arg(0);
		} else {
			$table_prefix='';
		}
		if(empty($table_prefix)) {
			$table_prefix='';
		}
		$constraints = $this->_constraints;
		$chain='';
		foreach ($constraints as $constraint) {
			$string = $constraint['constraint']->__toString($table_prefix);
			if (empty($string)) {
				continue;
			}
			$negate = ($constraint['negate'])?'NOT ':'';
			if (empty($chain)) {
				$chain=$negate.$string;
			} else {
				$chain.=' '.$constraint['type'].' '.$negate.$string;
			}
		}
		if (!empty($chain)) {
			$chain='('.$chain.')';
		}
		return $chain;
	}
	
	function count() {
		return count($this->_constraints);
	}
	
	function current() {
		return $this->_constraints[$this->_pointer];
	}
	
	function next() {
		$this->_pointer++;
	}
	
	function key() {
		return $this->_pointer;
	}
	
	function valid() {
		if(isset($this->_constraints[$this->_pointer]))
			return true;
		return false;
	}
	
	function rewind() {
		$this->_pointer=0;
	}
}

?>
