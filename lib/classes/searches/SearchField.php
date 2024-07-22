<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * Abstract class which, when extended, represents a component of a search.
 * This includes representing the component as a part of a form,
 * as well as representing the state of the component as a Constraint (or ConstraintChain if appropriate)
 */
abstract class SearchField {

	protected $version='$Revision: 1.17 $';

	protected $type;
	protected $fieldname;
	protected $label;
	protected $value;
	protected $value2;
	protected $default;
	protected $value_set = false;
	protected $do_constraint = true;

	/**
	 * @param $fieldname string
	 *
	 * Constructor.
	 */
	public function __construct($fieldname)
	{
		$this->fieldname = $fieldname;
	}

	/**
	 * @abstract
	 * @return string
	 * SearchFields should return the HTML representing them as to appear on a form.
	 * This should (currently) include the <label>, but no other markup
	 */
	abstract public function toHTML();

	/**
	 * @abstract
	 * @return mixed (Constraint or ConstraintChain)
	 * SearchFields should return a Constraint (or a ConstraintChain if appropriate) representing them as to be added to a SearchHandler
	 * Constraints are currently only AND'd together, so anything else will need to happen entirely within the field.
	 */
	abstract public function toConstraint();

	/**
	 * @param $fieldname string
	 *[@param $label string ]
	 *[@param $type string ]
	 *[@param $default mixed ]
	 *
	 * @return SearchField
	 * Returns the appropriate SearchField extension, derived from $type
	 */
	public static function Factory($fieldname, $label = null, $type = 'contains', $default = null, $do_constraint = true)
	{

		switch($type)
		{

			case 'hidden':	// hidden field to be used in searches
				$search = new HiddenSearchField($fieldname);
				break;

			case 'show':	// 'show' means that unless the box is checked, ($fieldname=false) will be used in the query
			case 'hide':	// 'hide' means that if the box is checked, then ($fieldname=true) will be used in the query
				$search = new CheckboxSearchField($fieldname);
				break;

			case 'contains':	// %s are placed before and after the search term in the LIKE part of the query
			case 'begins':		// a % will be placed at the end of the search term
			case 'ends':		// a % will be placed at the beginning of the search term 	(@TODO)
			case 'is':			// no wildcards are used, requires an exact match
				$search = new TextSearchField($fieldname);
				break;

			case 'greater':
			case 'greater_or_equal':
			case 'less_or_equal':
			case 'less':
			case 'equal':
			case 'not_equal':
				$search = new NumericSearchField($fieldname);
				break;

			case 'greater-integer':
			case 'greater_or_equal-integer':
			case 'less_or_equal-integer':
			case 'less-integer':
			case 'equal-integer':
			case 'not_equal-integer':
				$search = new IntegerSearchField($fieldname);
				$type = substr((string) $type, 0, -8);
				break;

			case 'before':		  // gets rows where the searchfield is before the entered value
			case 'beforeornull':  // gets rows where the searchfield is before the entered value or null
			case 'after':		  // gets rows where the searchfield is later than the entered value
			case 'afterornull':   // gets rows where the searchfield is later than the entered value or null
			case 'betweenfields': // gets rows where the entered date falls between 2 searchfields (opposite of between)
			case 'from': // gets rows where the entered date falls between 2 searchfields (opposite of between)
			case 'to': // gets rows where the entered date falls between 2 searchfields (opposite of between)
				$search = new DateSearchField($fieldname);
				break;

			case 'between':	      // gets rows where the searchfield falls between 2 entered dates
				$search = new TwoDateSearchField($fieldname);
				break;

			case 'ticket_status':	// a special Field for handling ticket statuses. 
				$search = new TicketStatusSearchField($fieldname);
				break;

			case 'order_status':	//order status has a similar special search
				$search = new OrderStatusSearchField($fieldname);
				break;

			case 'multi_select':	// a special Field for handling 'OR' for multiple fields. 
				$search = new MultiSelectSearchField($fieldname);
				break;

			case 'treesearch':	// a special Field for handling 'OR' for multiple fields. 
				$search = new TreeSearchField($fieldname);
				break;

			case 'porder_status':	// a special Field for handling 'OR' for multiple fields. 
				$search = new POrderSearchField($fieldname);
				break;

			case 'timeframe':
				$search = new TimeframeSearchField($fieldname);	//allows a choice from a number of timeframes to restrict by
				break;

			case 'select':
			case 'null':	      // gets rows where the searchfield is null or not null
				$search = new SelectSearchField($fieldname);
				break;

			case 'matrix':	      // gets rows where the searchfield is null or not null
				$search = new MatrixSearchField($fieldname);
				break;

			default:
				throw new Exception('No SearchField for type ' . $type);

		}

		$search->setType($type);
		$search->setLabel($label);
		$search->setDefault($default);

		$search->do_constraint = $do_constraint;

		return $search;

	}

	/**
	 * @param $type string
	 * @return void
	 *
	 * Sets the type of the SearchField
	 * Multiple types can be represented by each extension of SearchField, and they generally need to know what type they are
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @param void
	 * @return string
	 *
	 * Returns the HTML string containing the field's <label> tag.
	 * NOTE: at some point, this might end up being called from BaseSearch's toHTML to allow for containing elements
	 */
	protected function labelHTML()
	{

		// change to facilitate sortable fields
		$html = '<li><label for="search_' . str_replace('/', '_', $this->fieldname) . '">' . prettify($this->label) . '</label>';

		return $html;

	}

	/**
	 *[ @param $label string]
	 * @return void
	 *
	 * Set the label for the field. Accepts null, and defaults to prettify($fieldname)
	 */
	public function setLabel($label = null)
	{

		if ($label == null)
		{
			$label = $this->fieldname;
		}

		$this->label = $label;

	}

	public function getLabel()
	{
		return $this->label;		
	}

	/**
	 *[ @param value string]
	 *
	 * Set the default for the field, that will be used if no value is set
	 */
	public function setDefault($value = '')
	{
		$this->default = $value;
	}

	/**
	 * @param $value mixed
	 * @param &errors array
	 *
	 * Some fields need to be able to error if the value given isn't appropriate (e.g. numeric fields), this provides a mechanism for doing that
	 */
	public function isValid($value, &$errors = [])
	{
		return true;
	}

	/**
	 *[ @param $value string]
	 * 
	 * Set the value for the field. This will be used in place of a default
	 */
	public function setValue($value = '')
	{
		$this->value		= $value;
		$this->value_set	= true;
	}

	public function getValue()
	{

		if (!empty($this->value))
		{
			return $this->value;
		}

		return $this->default;

	}

	public function getDefault()
	{
		return $this->default;
	}

	public function getFieldname()
	{
		return $this->fieldname;
	}

	public function doConstraint()
	{
		return $this->do_constraint;
	}

	public function getCurrentValue()
	{

		if (!empty($this->value))
		{
			return $this->value;
		}

		if (!empty($this->default))
		{
			return $this->default;
		}

	}

}

// end of SearchField.php