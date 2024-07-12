<?php

/**
 *	Flash error handler
 *
 *	Utility class for storing and displaying errors to users in the UI
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */

class Flash {

	protected $version = '$Revision: 1.8 $';

	protected $_errors_show		= array();
	protected $_errors_store	= array();
	protected $_warnings_show	= array();
	protected $_warnings_store	= array();
	protected $_messages_show	= array();
	protected $_messages_store	= array();

	protected $noclear;

	function __construct($noclear = FALSE)
	{
		$this->noclear = $noclear;
		$this->restore();
	}

	public static function &Instance($noclear = FALSE)
	{

		static $Flash;

		if (empty($Flash))
		{
			$Flash = new Flash($noclear);
		}

		return $Flash;

	}

	public function save()
	{

		$this->_warnings_show	= $this->_warnings_store;
		$this->_messages_show	= $this->_messages_store;
		$this->_errors_show		= $this->_errors_store;

		$_SESSION['flash'] = $this;

	}

	public function restore()
	{

		if (empty($_SESSION['flash']))
		{
			$_SESSION['flash'] = array();
		}

		$temp = &$_SESSION['flash'];

		foreach ($temp as $key => $var)
		{
			$this->$key = $var;
		}

	}

	public function getMessages($type)
	{

		$return = array();

		if (in_array($type, array('errors', 'messages', 'warnings')))
		{
			$return = $this->{'_' . $type . '_show'};
			return $return;
		}

	}

	public function __get($var)
	{

		if (in_array($var, array('errors', 'messages', 'warnings')))
		{

			$return = $this->{'_' . $var . '_show'};

			if ($this->noclear !== TRUE)
			{
				$this->{'_' . $var . '_store'} = array();
				$this->save();
			}

			return $return;

		}

	}

	public function addError($error, $fieldname = NULL, $prefix = '')
	{

		$this->clearMessages();

		if (!in_array($error, $this->_errors_store))
		{

			if (!empty($fieldname))
			{
				$this->_errors_store[$prefix . $fieldname] = $error;
			}
			else
			{
				$this->_errors_store[] = $error;
			}

		}

	}

	public function addMessage($message, $fieldname = NULL, $prefix = '')
	{

		if (!in_array($message, $this->_messages_store))
		{

			if (!empty($fieldname))
			{
				$this->_messages_store[$prefix . $fieldname] = $message;
			}
			else
			{
				$this->_messages_store[] = $message;
			}

		}

	}

	public function addWarning($warning, $fieldname = NULL, $prefix = '')
	{

		if (!in_array($warning, $this->_warnings_store))
		{

			if (!empty($fieldname))
			{
				$this->_warnings_store[$prefix . $fieldname] = $warning;
			}
			else
			{
				$this->_warnings_store[] = $warning;
			}

		}

	}

	public function addErrors($errors, $prefix = '')
	{

		foreach ($errors as $fieldname => $error)
		{
			$this->addError($error, $fieldname, $prefix);
		}

	}

	public function addMessages($messages, $prefix = '')
	{

		foreach ($messages as $fieldname => $message)
		{
			$this->addMessage($message, $fieldname, $prefix);
		}

	}

	public function addWarnings($warnings, $prefix = '')
	{

		foreach ($warnings as $fieldname => $warning)
		{
			$this->addWarning($warning, $fieldname, $prefix);
		}

	}

	public function clear()
	{

		$this->_errors_store	= array();
		$this->_warnings_store	= array();
		$this->_messages_store	= array();

		unset($_SESSION['flash']);

	}

	public function shown() {
	}

	public function clearMessages()
	{
		$this->_messages_store = array();
	}

	public function hasErrors()
	{
		return count($this->_errors_store) != 0;
	}
}

// end of Flash.php
