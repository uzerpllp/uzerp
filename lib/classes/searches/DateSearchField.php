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

class DateSearchField extends SearchField {

	protected $version = '$Revision: 1.15 $';

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

		$value = $this->value;

		if (empty($this->value))
		{
			$value = $this->default;
		}

		$html = '<input type="text" class="icon date slim datefield" id="search_' . $this->fieldname . '" name="Search[' . $this->fieldname . ']" value="' . $value . '" /></li>';
		return $this->labelHTML() . $html;

	}


	/**
	 * @param void
	 * @return Constraint
	 *
	 * Produces a Constraint that uses either < or > for a comparison
	 * @TODO: implement 'between'
	 */
	public function toConstraint()
	{

		$c		= FALSE;
		$value	= '';

		if ($this->value_set)
		{
			$value = $this->value;
		}
		else
		{
			$value = $this->default;
		}

		if (!empty($value))
		{

			switch($this->type)
			{

				case 'before':
					$c = new Constraint($this->fieldname, '<', fix_date($value));
					break;

				case 'after':
					$c = new Constraint($this->fieldname, '>', fix_date($value));
					break;

				case 'betweenfields':
					$fields = explode('/', (string) $this->fieldname);

					if (count($fields) != 2)
					{
						break;
					}

					$c = new ConstraintChain();
					$c->add(new Constraint($fields[0], '<=', fix_date($value)));

					$c1 = new ConstraintChain();
					$c1->add(new Constraint($fields[1], '>=', fix_date($value)));
					$c1->add(new Constraint($fields[1], 'is', 'NULL'), 'OR');
					$c->add($c1);
					break;

				case 'beforeornull':
					$c=new ConstraintChain();
					$c->add(new Constraint($this->fieldname, '<', fix_date($value)));
					$c->add(new Constraint($this->fieldname, 'is', 'NULL'), 'OR');
					break;

				case 'afterornull':
					$c=new ConstraintChain();
					$c->add(new Constraint($this->fieldname, '>', fix_date($value)));
					$c->add(new Constraint($this->fieldname, 'is', 'NULL'), 'OR');
					break;

				case 'from':
					$c = new Constraint($this->fieldname, '>=', fix_date($value));
					break;

				case 'to':
					$c = new Constraint($this->fieldname, '<=', fix_date($value));
					break;
			}

		}

		return $c;

	}

	public function isValid($value, &$errors = [])
	{

		if (!empty($value))
		{

			if (!strtotime((string) fix_date($value)))
			{
				$errors[] = 'Search on ' . prettify($this->label) . ' needs to be a date';
				return FALSE;
			}
			elseif (date(DATE_FORMAT, strtotime((string) fix_date($value))) != $value)
			{
				$errors[] = 'Invalid date ' . $value . ' for search on ' . prettify($this->label);
				return FALSE;
			}

		}

		return TRUE;

	}

}

// end of DateDearchField.php