<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * Represents the special Ticket Status SearchField
 * HTML is a series of Checkboxes with individual labels and a common name
 * Constraint is an OR'd ConstraintChain with those values selected on the form
 */

class TicketStatusSearchField extends SearchField
{

	protected $version = '$Revision: 1.5 $';
	
	/**
	 * $value array
	 * The values for this SearchField is an array of $value=>'on' pairs
	 */
	protected $value = array();
	
	/**
	 * $statuses array
	 * the status codes that ticket statuses can be given
	 * @TODO: this should really be a property of TicketStatus
	 */
	protected $statuses = array(
		'NEW' ,
		'OPEN',
		'RESO',
		'CLSD' 
	);
		
	/**
	 * @param void
	 * @return string
	 *
	 * returns the HTML representation of the status checkboxes, each with a label
	 * NOTE: This SearchField doesn't have an encompassing label, so will need to be considered should containing elements be required
	 */
	public function toHTML()
	{
		
		$html = '';
		
		foreach ($this->statuses as $status)
		{
			
			$checked = '';
			
			if (($this->value_set && isset($this->value[$status])) || (!$this->value_set && in_array($status, $this->default)))
			{
				$checked = 'checked="checked"';
			}
			
			$html .= '<li class="checkbox"><label>' . prettify($status) . '</label><input type="checkbox" class="checkbox" name="Search[' . $this->fieldname . '][' . $status . ']" ' . $checked . '/></li>';
		}
		
		return $html;
		
	}
	
	/**
	 * @param void
	 * @return ConstraintChain
	 *
	 * Returns a constraintchain containing OR'd constraints for each status checked on the form
	 */
	public function toConstraint()
	{
		
		$cc = false;
		
		if (!is_array($this->value))
		{
			$this->value = array($this->value);
		}
		
		$cc		= new ConstraintChain();
		$codes	= ($this->value_set)?$this->value:array_flip($this->default);
		
		foreach ($codes as $code => $on)
		{
			
			if ($code != '')
			{
				$c = new Constraint($this->fieldname, '=', $code);
				$cc->add($c, 'OR');
			}
			
		}
		
		return $cc;
		
	}
	
	/**
	 * @param $value mixed
	 *
	 * Takes either a string of one, or an array of more than one, status(es) that should be considered the default(s)
	 */
	public function setDefault($value = array())
	{
		
		if (!is_array($value))
		{
			$value = array($value);
		}
		
		$this->default = $value;
		
	}
	
	/**
	 * @param $value mixed
	 * @return void
	 *
	 * Takes either a string or an array of statuses that have been selected
	 * A value which is null (===) will be ignored
	 */
	public function setValue($value = array())
	{
		
		if ($value !== null)
		{
			
			if (!is_array($value))
			{
				$value = array($value => 'On');
			}
			
			$this->value		= $value;
			$this->value_set	= true;
		
		}
		
	}

	public function getCurrentValue()
	{
		return implode(',', array_keys($this->value));
	}
	
}

// end of TicketStatusSearchField.php