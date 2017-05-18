<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SessionData
{

	protected $version = '$Revision: 1.3 $';
	
	/*
	 * The key identify the persistent session data
	 */
	private $key;
	
	/*
	 * Persistent data array with keys 'fields' and 'values'
	 * 
	 * 	  array('fields'=>	array(field)
	 * 			'values' =>	array(key => array(field => value)))
	 * 
	 * Fields is an array of field names for which data is to be stored
	 * Values is an array of key/field values
	 * 		- the key uniquely identifies an array of values
	 * 		- the array of values are the field=>value pairs where the field
	 * 			exists in the 'fields' array
	 */
	private $data = array();
	
	/*
	 * 	Data Object to maintain paged data between page requests
	 */
	public function __construct($key)
	{
		$this->key = $key;
		
		// get the saved session data for this key if it exists
		if (isset($_SESSION['persistent_data'][$this->key]))
		{
			$this->data = $_SESSION['persistent_data'][$this->key];
		}
		else
		{
			$this->save();
		}
	}
	
	/*
	 * 	clear
	 * 
	 * 	Unsets the session data for page key
	 */
	public function clear()
	{
		unset($_SESSION['persistent_data'][$this->key]);

		$this->data = array();
	}
	
	/*
	 * 	save
	 * 
	 * 	Saves the current session page data (fields and values)
	 */
	public function save()
	{
		$_SESSION['persistent_data'][$this->key] = $this->data;
	}
	
	/*
	 * 	registerPageData
	 * 
	 * 	Sets the fields to be stored for the session page data
	 * 	Any existing data for the key will be lost
	 */
	public function registerPageData($fields)
	{
		$this->data = array('fields' => $fields);
		
		$this->save();
	}
	
	/*
	 * 	PageDataExists
	 * 
	 * 	Check if session data exists for the page key
	 */
	public function PageDataExists()
	{
		return (!empty($this->data));
	}
	
	/*
	 * 	getPageData
	 * 
	 * 	returns the stored session page data
	 *  or an empty array if no values have yet been stored
	 */
	public function getPageData()
	{
		if (isset($this->data['values']))
		{
			return $this->data['values'];
		}
		else
		{
			return array();
		}
	}
	
	/*
	 * 	clearPageData
	 * 
	 * 	Clears the values for the session data key
	 */
	public function clearPageData()
	{
		if (isset($this->data['values']))
		{
			unset($this->data['values']);
		}
		
		$this->save();
	}
	
	/*
	 * 	deletePageData
	 * 
	 * 	Deletes an entry for a sepcified key value
	 */
	public function deletePageData($id)
	{
		if (isset($this->data['values'][$id]))
		{
			unset($this->data['values'][$id]);
		}
		
		$this->save();
	}
	
	/*
	 * 	updatePageData
	 * 
	 * 	Updates the stored session data for the specified key value
	 * 	with the supplied field values overwriting any existing values
	 */
	public function updatePageData($id, $fields, &$errors = array())
	{
		if ($this->PageDataExists())
		{
			foreach ($this->data['fields'] as $fieldname)
			{
				$this->data['values'][$id][$fieldname] = $fields[$fieldname];
			}
			
			$this->save();
		}
		else
		{
			$errors[] = $this->keyNotFound();
		}
		
	}
	
	/*
	 * 	addPageData
	 * 
	 * 	Insert into the stored session data for the specified key value
	 *  with the supplied field values where the specified key value does not exist
	 */
	public function addPageData($id, $fields, &$errors = array())
	{
		if ($this->PageDataExists())
		{
			foreach ($this->data['fields'] as $fieldname)
			{
				if (!isset($this->data['values'][$id][$fieldname]))
				{
					$this->data['values'][$id][$fieldname] = $fields[$fieldname];
				}
			}
			
			$this->save();
		}
		else
		{
			$errors[] = $this->keyNotFound();
		}
		
	}
	
	/*
	 * Private functions
	 */
	private function keyNotFound()
	{
		return 'Session persistent_data not found for key '.$this->key;
	}
	
}

// End of SessionData
