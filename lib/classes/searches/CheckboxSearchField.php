<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * For SearchFields that can be represented by a Checkbox on a form
 * - this doesn't mean they have to be representing a boolean DB-field, look at setOnValue()
 */

class CheckboxSearchField extends SearchField {

	protected $constraint;
	protected $off_value;

	/**
	 * $checked boolean
	 * whether or not the checkbox was checked when the user submitted a form
	 */
	protected $checked = FALSE;

	/**
	 * $default boolean
	 * whether the checkbox should be selected by default, i.e. when the form hasn't been submitted
	 */
	protected $default = FALSE;

	/**
	 * $on_value string
	 * What the 'value' attribute of the input should be.
	 * This is (probably) to be used when representing an equality surrounding a non-boolean field, for example 'my tickets' has on_value of the current user's person_id
	 */
	protected $on_value = 'true';

	public function toHTML()
	{
		$html = '<input type="checkbox" class="checkbox" value="' . $this->on_value . '" name="Search[' . $this->fieldname . ']" id="search_' . $this->fieldname . '" ' . (($this->checked) || (!$this->value_set && $this->default)?'checked="checked"':'') . " /></li>" . "\n";
		return $this->labelHTML() . $html;
	}

	/**
	 * @param void
	 * @return Constraint
	 *
	 * For 'show' type checkboxfields, the constraint is of the form ($this->fieldname,'=','false) iff:
	 * (the form has been submitted && the checkbox was unchecked) OR (the form hasn't been submitted and the default is set to false)
	 * i.e. if the form is submitted with the checkbox checked, then the constraint won't be added
	 *
	 * For 'hide' type checkboxfields, the constraint is of the form ($this->fieldname,'=',$this->value) iff:
	 * (the form has been submitted with the checkbox checked) OR (the form hasn't been submitted and the dfault is set to true)
	 *
	 */
	public function toConstraint()
	{

		$c = FALSE;

		if ($this->type == 'show')
		{

			if (($this->value_set && !$this->checked) || (!$this->value_set && !$this->default))
			{

				if (isset($this->off_value))
				{
					$val = $this->off_value;
				}
				else
				{
					$val = 'false';
				}

				$c = new Constraint($this->fieldname, '=', $val);

			}

		}
		elseif ($this->type == 'hide')
		{

			if (($this->value_set && $this->checked) || (!$this->value_set && $this->default))
			{

				if (!$this->value_set)
				{
					$this->value = 'true';
				}

				if (isset($this->constraint))
				{
					$c = $this->constraint;
				}
				else
				{

					if (isset($this->on_value))
					{
						$val = $this->on_value;
					}
					else
					{
						$val = $this->value;
					}

					$c = new Constraint($this->fieldname, '=' ,$val);

				}

			}

		}

		return $c;

	}

	/**
	 * @param $constraint Constraint(Chain)
	 * 
	 * Allows the constraint to be over-ridden. Only understood by 'hide' type fields at the moment.
	 */
	public function setConstraint($constraint)
	{
		$this->constraint = $constraint;
	}

	/**
	 * @param string (value will be used in a string-context, so values can be numeric)
	 * @return void
	 *
	 * Sets the on-value for the checkbox. This is the value that will be present when the form is submitted
	 */
	public function setOnValue($value)
	{
		$this->on_value = $value;
	}

	public function setOffValue($value)
	{
		$this->off_value = $value;
	}

	/**
	 * @param string 
	 * @return void
	 *
	 * Set the value of the searchfield. This will typically be 'on', unless setOnValue() is used
	 * A $value that is (===) null will be ignored
	 */
	public function setValue($value = '')
	{

		if ($value == 'on')
		{
			$this->checked	= TRUE;
			$this->value	= 'true';
		}
		elseif ($value !== null)
		{
			$this->checked	= TRUE;
			$this->value	= $value;
		}

		$this->value_set = TRUE;

	}

	/**
	 * @param string
	 * @return void
	 *
	 * Set the default value of the checkbox. Anything other than 'checked' is assumed to be false
	 */
	public function setDefault($value = '')
	{

		if ($value == 'checked')
		{
			$this->default = TRUE;
		}
		else
		{
			$this->default = FALSE;
		}

	}

	public function getCurrentValue()
	{
		return ucwords((string) $this->value);
	}

}

// end of CheckboxSearchField.php