<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * A SearchField for searching based on date
 *
 */

class TwoDateSearchField extends SearchField {

	protected $version = '$Revision: 1.17 $';

	/**
	 * @param void
	 * @return string
	 *
	 * Returns the HTML to represent the datafield-type input.
	 * The class of 'datefield' causes the datepicker icon (and functionality...) to be added
	 * @BUG: Due to the lack of containing elements, this field must be added last else the icon will be in the wrong place
	 */
	public function toHTML()
	{

		if (!empty($this->value['from']))
		{
			$from = $this->value['from'];
		}
		elseif (!empty($this->default['from']))
		{
			$from = $this->default['from'];
		}
		else
		{
			$from = '';
		}

		if (!empty($this->value['to']))
		{
			$to = $this->value['to'];
		}
		elseif (!empty($this->default['to']))
		{
			$to = $this->default['to'];
		}
		else
		{
			$to = '';
		}

		$html = '<input type="text" class="icon date slim datefield" id="search_' . $this->fieldname . '" name="Search[' . $this->fieldname . '][from]" value="' . $from . '" /><span class="search-twofield-sep"> to </span><input type="text" class="icon date slim datefield" id="search_' . $this->fieldname . '_to" name="Search[' . $this->fieldname . '][to]" value="' . $to . '" /></li>';
		return $this->labelHTML() . $html;

	}

	/**
	 * @param void
	 * @return Constraint
	 *
	 * Produces a Constraint between two dates
	 */
	public function toConstraint()
	{

		$db		= DB::Instance();
		$c		= false;
		$to		= '';
		$from	= '';

		if ($this->value_set)
		{
			$from	= $this->value['from'];
			$to		= $this->value['to'];
		}
		else
		{
			$from	= $this->default['from'];
			$to		= $this->default['to'];
		}

		switch($this->type)
		{

			case 'between':

				if(!empty($from))
				{

					if (!empty($to))
					{
						$c = new Constraint($this->fieldname . '::date', 'between', $db->qstr(fix_date($from)) . ' and ' . $db->qstr(fix_date($to)));
					}
					else
					{
						$c = new Constraint($this->fieldname . '::date', '>=', $db->qstr(fix_date($from)));
					}

				}
				else
				{

					if (!empty($to))
					{
						$c = new Constraint($this->fieldname . '::date', '<=', $db->qstr(fix_date($to)));
					}

				}

				break;

		}

		return $c;

	}

	public function setDefault($value = array())
	{

		if (!is_array($value))
		{
			$value = array($value);
		}

		$this->default = $value;

	}

	public function isValid($value, &$errors = [])
	{

		if (!is_array($value))
		{
			$errors[] = 'Search on ' . prettify($this->label) . ' needs to be a date pair';
			return false;
		}
		else
		{

			$prevdate = '';

			foreach ($value as $key => $date)
			{

				if (!empty($date))
				{

					if (!empty($prevdate) && $prevdate > fix_date($date))
					{
						$errors[] = 'Search on ' . prettify($this->label) . ' date range invalid';
						return false;
					}
					elseif (!strtotime((string) fix_date($date)))
					{
						$errors[] = 'Search on ' . prettify($this->label) . ' needs to be a date';
						return false;
					}
					elseif (date(DATE_FORMAT, strtotime((string) fix_date($date))) != $date)
					{
						$errors[] = 'Invalid date ' . $date . ' for search on ' . prettify($this->label);
						return false;
					}

				}

				$prevdate = fix_date($date);

			}

		}

		return true;

	}

	public function getCurrentValue()
	{

		$errors	= array();
		$to		= '';
		$from	= '';

		if ($this->value_set)
		{
			$from	= $this->value['from'];
			$to		= $this->value['to'];
		}
		else
		{
			$from	= $this->default['from'];
			$to		= $this->default['to'];
		}

		// both values cannot be empty
		if (empty($from) && empty($to))
		{
			return FALSE;
		}

		if (empty($from))
		{
			$from = 'Beginning of time';
		}

		if (empty($to))
		{
			$to = 'End of time';
		}

		return $from . ' to ' . $to;

	}

}

// end of TwoDateSearchField.php