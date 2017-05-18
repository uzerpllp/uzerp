<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/**
 * I think this is necessary as more is needed from DB-fields than ADOFieldObject allows for
 * but extending it wouldn't work as that would require changing bits of adodb code, and we don't want to do that.
 * Is this maybe a decorator?
 */

class DataField {

	protected $version = '$Revision: 1.13 $';

	// ADOFieldObject: the field object that the DataField is encapsulating
	public	$_field;
	public	$isHandled				= false;
	public	$html					= false;
	// array: Validators associated with the field
	public	$_validators			= array();

	private $defaults_set			= false;
	private $formatters_set			= false;
	private $default_callback		= false;
	private $hasValidators			= false;
	private $_blocked_validators	= array();

	private	$_formatter				= null;

/*
 * The ADODB object returns the following attributes:-
    [name]
    [max_length]
    [type]
    [attnum]
    [scale]
    [has_default]
    [default_value]
    [not_null]

	DataObject::setFields adds the following attributes:-
	[value]
    [system_default_value] - this is the default value from the data base or the data model
    [user_defaults_allowed] - defines whether the user can define defaults through Module Defaults

	DataObject::setDefaultFieldValues adds the following attributes:-
	[display_default_value] - used by Module Defaults

 */

	/**
	 * Constructor
	 * Turns an ADOFieldObject into a more useful Object
	 * (ADOFieldObject currently (2006/07/03) doesn't have any functions, but will need to probably check future versions don't and think about __calling them...)
	 *
	 * @param	ADOFieldObject	An initialised ADOFieldObject
	 * @see ADOFieldObject
	 */
	public function __construct($field, $value = null)
	{

		if ($field instanceof ADOFieldObject)
		{

			$this->_field = $field;

			if (!isset($field->value))
			{
				$this->_field->value = null;
			}

		}
		else
		{
			$this->_field			= new ADOFieldObject();
			$this->_field->name		= $field;
			$this->_field->value	= $value;
		}

		$files = array('image', 'thumbnail', 'file_id', 'fileid');

		if (in_array($this->name, $files))
		{
			$this->type = 'file';
		}

		//$this->setDefaultFormatters();
		//$this->setDefaultValues();

		$ignore = array('created', 'lastupdated', 'alteredby', 'usercompanyid');

		if (in_array($this->name, $ignore))
		{
			$this->type = 'ignore';
		}

		if ($this->_field->type == 'yearperiod')
		{
			$this->max_length = '5';
		}

	}

	/**
	 * Register a Validator against the field.
	 * Passed argument must be an object that implements iTestable
	 *
	 */
	public function addValidator(FieldValidation $validator)
	{

		if (!in_array(get_class($validator), $this->_blocked_validators))
		{
			$this->_validators[] = $validator;
			$this->blockValidator(get_class($validator));
		}

	}

	public function blockValidator($validator_name)
	{
		$this->_blocked_validators[] = $validator_name;
	}

	/**
	 * Sets up FieldValidators for the field based on it's DB-properties
	 *
	 * @todo	Probably some more things could go here? (date-types for example, cna compulsory fields!...)
	 */
	private function setDefaultValidators()
	{

		if ($this->type == 'bool')
		{
			$this->addValidator(new BooleanValidator());
		}

		if (!empty($this->_field->not_null) && $this->_field->not_null == 1)
		{
			$this->addValidator(new PresenceValidator());
		}

		if ($this->type == 'date' || $this->type == 'timestamp')
		{
			$this->addValidator(new DateValidator());
			$this->is_date = true;
		}

		if (substr($this->type, 0, 3) == 'int' || $this->type == 'rate' || $this->type == 'numeric' || $this->type == 'glref')
		{
			$this->type = 'numeric';
			$this->addValidator(new NumericValidator());
		}

		if ($this->name == 'password')
		{
			$this->addValidator(new PasswordValidator());
		}

 		if ($this->name == 'username')
		{
		    $this->addValidator(new UsernameValidator());
		}

	}

	private function setDefaultFormatters()
	{

		if ($this->type == 'date')
		{
			$this->_formatter = new DateFormatter();
		}

		if ($this->type == 'bool')
		{
			$this->_formatter = new BooleanFormatter();
			$this->_formatter->is_html = $this->html;
		}

		if ($this->type == 'timestamp' || $this->name == 'created' || $this->name == 'lastupdated')
		{
			$this->_formatter = new TimestampFormatter();
		}

//		if (substr($this->name,-5)=='price') {
//			$this->_formatter = new PriceFormatter();
//		}

		$this->formatters_set = true;

	}

	public function formatted()
	{
		return $this->formatted;
	}

	public function setFormatter(FieldFormatter $formatter)
	{
		$this->_formatter = $formatter;
	}

	/**
	 * For defaults that aren't static
	 *
	 */
	private function setDefaultValues()
	{

		$db = &DB::Instance();

		switch ($this->name)
		{

			case 'owner':

				$this->has_default = true;
				if (defined('EGS_USERNAME'))
				{
					$this->default_value=EGS_USERNAME;
				}
				break;

			// Need to move this out to a configuration record
			case 'lang':

				$this->has_default = true;

				if (defined('EGS_USERNAME'))
				{
					$query = 'SELECT lang FROM person p JOIN users u ON (p.id=u.person_id) WHERE u.username=' . $db->qstr(EGS_USERNAME);
					$this->default_value = $db->CacheGetOne($query);
				}

				if (empty($this->default_value))
				{
					$this->default_value = 'EN';
				}

				break;

			case 'countrycode':

				// Need to move this out to a configuration record
				$this->has_default = true;

				if (defined('EGS_USERNAME'))
				{
					$query = 'SELECT countrycode FROM personaddress pa JOIN users u ON (pa.person_id=u.person_id)'.
							 'WHERE u.username='.$db->qstr(EGS_USERNAME).' ORDER BY main DESC';

					$country = $db->GetOne($query);

					if ($country === false)
					{
						$query = 'SELECT countrycode FROM users u JOIN person p ON (p.id=u.person_id) JOIN companyaddress ca ON (p.company_id=ca.company_id) '.
							'WHERE u.username=' . $db->qstr(EGS_USERNAME) . ' ORDER BY main DESC';
						$country = $db->GetOne($query);
					}

				}

				if (!isset($country))
				{
					$country = 'GB';
				}

				$this->default_value = $country;

				break;

			case 'assigned':
			case 'assigned_to':

				if (defined('EGS_USERNAME'))
				{
					$this->has_default		= true;
					$this->default_value	= EGS_USERNAME;
				}

		}

		if ($this->default_callback !== false)
		{
			$this->default_value	= call_user_func($this->default_callback, $this);
			$this->has_default		= true;
		}

		$this->defaults_set = true;

	}

	public function dropDefault()
	{
		$this->has_default		= false;
		$this->default_value	= null;
		$this->defaults_set 	= true;
	}

	public function setDefaultCallback($callback)
	{
		$this->default_callback=$callback;
	}

	/**
	 * Tests a field for being valid against its validators
	 * @param	&$errors	An array passed-by-reference that has error messages put into it
	 * @return	mixed		boolean-false on failure,
	 */
	public function test($value, &$errors=array())
	{
        // If no errors have been added yet, set $errors to an empty array
        // before calling validators.
	    if (!isset($errors)) {
            $errors = [];
        }

	    if (!$this->hasValidators)
		{
			$this->setDefaultValidators();
			$this->hasValidators = true;
		}

		if (count($this->_validators)==0)
		{
			return $value;
		}

		$this->value = $value;

		foreach ($this->_validators as $validator)
		{

			$this->value = $validator->test($this, $errors);

			if ($this->value === false)
			{
				return false;
			}

		}

		return $this->finalvalue;

	}

	/**
	 * Formats date according to user preference
	 *
	 *@param	string	Date in format yyyy-mm-dd
	 *@return	string	Date in user preferred format
	 */
	public function formatDate($date)
	{

		$timestamp = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
		$formatted = date(DATE_FORMAT, $timestamp);

		return $formatted;

	}

	/**
	 * Allows for the getting of the ADOFieldObject's properties
	 *
	 *@param	string	The name of the property
	 *@return	mixed	The value of the ADOFieldObject's corresponding property
	 */
	public function __get($var)
	{

		if (substr($var, 0, 9) == 'formatted')
		{

			if (!$this->formatters_set)
			{
				$this->setDefaultFormatters();
			}

			if (isset($this->_formatter))
			{
				return $this->_formatter->format($this->_field->value);
			}
			else
			{
				$var = 'value';
			}

		}

		if ($var == 'is_safe')
		{

			if (isset($this->_formatter))
			{
				return ($this->_formatter->is_safe === true);
			}

			return false;

		}

		if (($var == 'has_default' || $var == 'default_value') && $this->defaults_set == false)
		{
			$this->setDefaultValues();
		}

		if ($var == 'tag')
		{

			$tag = '';

			if (isset($this->_field->tag))
			{
				$tag = $this->_field->tag;
			}

			if (empty($tag))
			{
				$name		= $this->name;
				$this->tag	= prettify($name);
			}

		}

		//if($var=='value'&&$this->name=='size') {
		//	return sizify($this->_field->value);
		//}

		if ($var == 'default_value' && ($this->_field->default_value == 'now()'))
		{
			return time();
		}

		if ($var == 'default_value' && substr($this->_field->default_value, -19) == '::character varying')
		{

			$value = str_replace('::character varying', '', $this->_field->default_value);

			if (substr($value, 0, 1) == "'" && substr($value, -1) == "'")
			{
				return str_replace("'", '', $value);
			}
			else
			{
				return $value;
			}

		}

		if ($this->_field->type == 'interval' && $var == 'default_value')
		{

			if ($this->_field->default_value == "'00:00:00'::interval")
			{
				return array(0, 'hours');
			}

		}

		if (isset($this->_field->$var))
		{
			return $this->_field->$var;
		}

		if ($var == 'finalvalue')
		{

			$thevalue = $this->_field->value;

			if (isset($thevalue))
			{
				return $thevalue;
			}
			else
			{
				return null;
			}

		}

	}

	public function setDefault($value)
	{

		// Defaults can be set as follows:-
		//	1) within the DataObject
		//	2) as a column default within the database
		//	3) as a system override within Modules under Module Components/Defaults
		// The order of precedence is
		//	column default overrides DataObject default
		//	Module Components/Default overrides column default

		if (!$this->has_default)
		{
			$this->has_default				= 1;
			$this->default_value			= $value;
			$this->display_default_value	= $value;
		}

	}

	public function setnotnull()
	{
		$this->not_null = true;
	}

	public function dropnotnull()
	{
		$this->not_null = false;
	}

	public function clearValue()
	{
		$this->value = "";
	}


	 //****************
	// MAGIC FUNCTIONS

	public function __set($var, $val)
	{
		$this->_field->$var = $val;
	}

	function __clone()
	{
		$this->_field = clone($this->_field);
	}

}

// end of DataFeild.php