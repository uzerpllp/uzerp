<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class QueryBuilder {

	protected $version = '$Revision: 1.12 $';
	
	private $db;
	private $doname;
	private $tablename = '';
	private $model;
	private $fields;
	private $field_array = array();
	private $groupby_string = '';
	private $order_string = '';
	private $limit_string = '';
	private $distinct = false;
	private $select_string = '';
	private $from_string = '';
	private $where_string = '';
	
	public function __construct($db, $do = null)
	{
		
		$this->db = $db;
		
		if (isset($do))
		{
			
			if ($do instanceof DataObject)
			{
				$this->doname	= get_class($do);
				$this->model	= $do;
			}
			elseif ($do instanceof DataObjectCollection)
			{
				$this->model	= $do->getModel();
				$this->doname	= get_class($this->model);
			}
			else
			{
				$this->doname = $do;
			}
			
		}
		
	}
	
	public function setDistinct($distinct = true)
	{
		$this->distinct = $distinct;
	}
	
	private function getDO()
	{
		
//		echo 'QueryBuilder::getDO : start '.$this->doname.'<br>';		
		if (empty($this->model))
		{
			$this->model = new $this->doname;
		}
		
		return $this->model;
		
	}
	
	public function select($fields)
	{
		
		$this->field_array	= $fields;
		$this->fields		= '';
		$fields_array		= array();
		
		if (!empty($this->doname))
		{
			$do = $this->getDO();
		}
		
		if (is_array($fields) && count($fields) > 0)
		{
			
			foreach ($fields as $fieldname => $field)
			{
				
				if (isset($do))
				{
					
					if (isset($do->concatenations[$fieldname]))
					{
						
						foreach ($do->concatenations[$fieldname]['fields'] as $concatfield)
						{
							$fields_array[] = $concatfield;
						}
						
					}
					elseif (is_object($field))
					{
						$fields_array[] = $field->name;
					}
					
				}
				
			}
			
			$this->fields = implode(',', $fields_array);
			
//			if (count($fields) == 2)
//			{
//				$this->fields .=', \'blanking\' as blanking';
//			}
			
		}
		else
		{
			
			if (isset($do) && $do->isField($do->idField))
			{
				$this->fields = $do->idField . ', *';
			}
			else
			{
				$this->fields = '*';
			}
			
		}
		
		if ($this->fields == ', *')
		{
			$this->fields = '*';
		}
		
		if (empty($this->fields))
		{
			$this->select_string = '';
		}
		else
		{
			$this->select_string = 'SELECT ' . ($this->distinct ? ' DISTINCT ' : '') . $this->fields;
		}
		
		return $this;
		
	}
	
	public function delete()
	{
		$this->select_string = 'DELETE ';
		return $this;
	}
	
	public function update($tablename)
	{
		$this->select_string = 'UPDATE ' . $tablename;
		return $this;
	}
	
	public function update_fields($fields, $values)
	{
		
		if (!is_array($fields))
		{
			$fields = array($fields);
		}
		
		if (!is_array($values))
		{
			$values = array($values);
		}
		
		$field_values = array();
		
		if (count($fields) == count($values))
		{
			
		 	foreach ($values as $index => $value)
		 	{
		 		
				if ($value !== 'null' && substr($value, 0, 1) != '(')
				{
					$field_values[$index] = $fields[$index] . ' = ' . $this->db->qstr($value);
		 		}
		 		else
		 		{
		 			$field_values[$index] = $fields[$index] . ' = ' . $value;
		 		}
		 		
			}
			
		}
		
		$this->from_string = ' SET ' . implode(',', $field_values);
		
		return $this;
		
	}
	
	public function from($tablename)
	{
		
		$this->tablename	= $tablename;
		$this->from_string	= 'FROM ' . $tablename;
		
		return $this;
		
	}

	public function where($constraints)
	{
		
		$constraintString = $constraints->__toString();
		
		if (!empty($constraintString))
		{
			$this->where_string = 'WHERE ' . $constraintString;
		}
		else
		{
			$this->where_string = '';
		}
		
		return $this;
		
	}

	public function groupby($groupby)
	{
		
		if (!is_array($groupby))
		{
			$groupby = array($groupby);
		}
		
		$groupbystring = '';
		
		foreach ($groupby as $i => $fieldname)
		{
			
			if (count($this->field_array) > 0)
			{

				if (!empty($fieldname) && isset($this->field_array[$fieldname]))
				{
					$groupbystring .= $fieldname . ', ';
				}
				
			}
			
		}
		
		if (!empty($groupbystring))
		{
			$groupbystring			= substr($groupbystring, 0, -2);
			$this->groupby_string	= 'GROUP BY ' . $groupbystring;
		}
		
		return $this;
		
	}

	/**
	 * Set the 'ORDER BY' part of the query.
	 * 
	 * Takes either a string for each argument, or an array for each argument (mixture not advised)
	 * @param Array|String $orderby
	 * @param Array|String $orderdir
	 * 
	 * @return QueryBuilder
	 */
	public function orderby($orderby, $orderdir)
	{
		if (!is_array($orderby))
		{
			$orderby = array($orderby);
		}
		
		if (!is_array($orderdir))
		{
			$orderdir = array($orderdir);
		}
		
		$orderstring = '';
		
		foreach ($orderby as $i => $fieldname)
		{
			// orderby is potential user input, it may have come via a URL parameter on an index view
			// Ignore if it doesn't match a model field.
			//
			// Note that postgres supports CASE statements in orderby, which we don't use and can
			// be leveraged for SQL injection.
			if (isset($this->model) && $this->model->getField($fieldname) === false) continue;

			if (is_array($this->field_array) && count($this->field_array) > 0)
			{
				
				if (!empty($fieldname) && !array_key_exists(strtolower($fieldname), $this->field_array))
				{
					$this->select_string .= ',' . $fieldname;
				}
				
			}
			
			if (!empty($fieldname))
			{
				$orderstring .= $fieldname . ' ' . (!empty($orderdir[$i]) ? $orderdir[$i] : 'ASC') . ', ';
			}
			
		}
		
		if (!empty($orderstring))
		{
			$orderstring		= substr($orderstring, 0, -2);
			$this->order_string	= 'ORDER BY ' . $orderstring;
		}
		
		return $this;
		
	}

	public function limit($limit, $offset)
	{
		
		if (!empty($limit))
		{
			
			$this->limit_string = 'LIMIT ' . $limit . ' ';
			
			if ($offset != '')
			{
				$this->limit_string .= 'OFFSET ' . $offset;
			}
			
		}
		
		return $this;
		
	}

	public function __toString()
	{
		
		return implode(
			' ',
			array(
				$this->select_string,
				$this->from_string,
				$this->where_string,
				$this->groupby_string,
				$this->order_string,
				$this->limit_string
			)
		);
		
	}
	
	public function countQuery($id = '')
	{
		
		if (empty($id))
		{
			
			if (current($this->field_array) instanceof DataField)
			{
				$id = strtolower(current($this->field_array)->name);
			}
			else
			{
				$id = implode($this->model->identifierFieldJoin, array_keys($this->field_array));
			}
			
		}
		
		if (is_array($id))
		{
				$id = implode($this->model->identifierFieldJoin, $id);
		}
		elseif (strpos($id, ' as '))
		{
			// field name has alias
			$id = substr($id, 0, strpos($id, ' as '));
		}
		
		
		if (empty($id))
		{
			$id = 'id';
		}
		
		if ($this->distinct)
		{
			$string = 'SELECT COUNT(DISTINCT ' . $id . ')';
		}
		else
		{
			$string = 'SELECT count(*) ';
		}
		
		$string .= $this->from_string . ' ' . $this->where_string;
		
		return $string;

	}
	
}

// end of QueryBuilder.php