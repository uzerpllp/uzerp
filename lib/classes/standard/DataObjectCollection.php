<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DataObjectCollection implements Iterator, Countable {
	
	protected $version			= '$Revision: 1.57 $';
	protected $_dataobjects		= array();
	protected $_pointer			= 0;
	protected $_fields;
	protected $display_fields;
	protected $_templateobject	= null;
	protected $_valid;
	protected $data;
	
	public $_doname;
	public $_tablename;
	public $orderby				= null;
	public $direction			= null;
	public $limit				= 10;
	public $page				= 1;
	public $pages				= 1;
	public $offset				= 0;
	public $records				= 0;
	public $search;
	public $searchField;
	public $searchString		= '';
	public $headings			= array();
	public $clickcontroller		= '';
	public $clickaction			= '';
	public $editclickaction		= '';
	public $deletecontroller	= '';
	public $deleteclickaction	= '';
	public $newtext				= '';
	public $sh					= null;
	public $query				= '';
	public $title;
	
	private $load_options = array('RETURN_QUERY'	=> 1
								 ,'RETURN_COUNTS'	=> 2
								 ,'RETURN_ROWS'		=> 3
								 ,'RETURN_OBJECTS'	=> 4);
	
	function __construct($do = '', $tablename = '')
	{
		
		foreach ($this->load_options as $type=>$value)
		{
			if (!defined($type))
			{
				define($type , $value);	
			}
		}
		
		if (empty($do))
		{
			$do = str_replace('Collection', '', get_class($this));	
		}
		
		if (is_string($do))
		{
			
			$this->_doname = $do;
			
			if (empty($tablename))
			{
				$temp = DataObjectFactory::Factory($do);
			}
			else
			{
				$temp = DataObjectFactory::Factory($do, $tablename);
			}
			
		}
		else
		{
			$temp			= $do;
			$this->_doname	= get_class($temp);
		}

		$this->usercontrolled	= $temp->isField('usercompanyid');
		$this->_templateobject	= $temp;
		
		if (empty($tablename))
		{
			$this->_tablename = $temp->getTableName();
		}
		else
		{
			
			$this->_tablename = $tablename;
			
			if ($temp->getTableName() != $tablename)
			{
				$temp->setViewName($tablename);
			}
			
		}
		
		$this->display_fields = $temp->getDisplayFields();
					
		$this->_fields = $this->display_fields;
		
		$this->idField = $temp->idField;
		
		if ($this->orderby == null)
		{
			$this->orderby = $temp->getDefaultOrderby();
		}
		
		if ($this->direction == null)
		{
			$this->direction = $temp->orderdir;
		}
		
		if (empty($this->direction))
		{
			$this->direction = 'ASC';
		}
		
		$this->_valid = $temp->isValid();
		
		unset($temp);
		return $this->_valid;
		
	}

	function add($do)
	{
		
		if (is_array($do))
		{
			
			foreach ($do as $object)
			{
				
				if ($object instanceof $this->_doname)
				{
					$this->_dataobjects[] = $object;
				}
				
			}	
			
		}
		elseif ($do instanceof $this->_doname)
		{
			$this->_dataobjects[] = $do;
		}
		
	}

	function getArray()
	{
		return $this->data;
	}
	
	function getViewName()
	{
		return $this->_tablename;
	}

	protected function addAuditFields(&$fields)
	{
		
		// get the fields for the base table/view for the collection
		// and add the audit fields if they exist in the table/view
		
		$do = new DataObject($this->_tablename);
		
		foreach ($do->audit_fields as $audit_field)
		{
			
			if ($do->isField($audit_field))
			{
				$fields[$audit_field] = $do->getField($audit_field);
			}
			
		}
		
	}
	
	public function addSystemRules($sh = '')
	{
		
		if (defined('SYSTEM_POLICIES_ENABLED') && SYSTEM_POLICIES_ENABLED)
		{
			if ($this->_templateobject instanceof DataObject)
			{
				$do = $this->_templateobject;
			}
			else
			{
				$do = DataObjectFactory::Factory($this->_doname, $this->_tablename);
			}
			
			if (isset($do->_policyConstraint['constraint']) && $do->_policyConstraint['constraint'] instanceof ConstraintChain)
			{
				$constraint = $do->_policyConstraint;
				
				if (isset($constraint['field']) && ($sh instanceof SearchHandler))
				{
					foreach ($constraint['field'] as $fieldname)
					{
						if (in_array($fieldname, $sh->fields))
						{
							$constraint->removeByField($fieldname);
						}
					}
				}
				
				return $constraint;
			}
		}
		
		return array('name' => array()
					,'constraint' => new ConstraintChain());
		
	}
	
	// TODO: Change this to protected and amend anything external to the class
	// to call load with type RETURN_QUERY
	public function buildQuery($sh, $qb)
	{
		
		$query = $qb
			->select($this->_fields)
			->from($this->_tablename)
			->where($sh->constraints)
			->groupby($sh->groupby)
			->orderby($sh->orderby, $sh->orderdir)
			->limit($sh->perpage, $sh->offset)
			->__toString();
			
		return $query;

	}
	
	protected function _load($sh, $qb, $c_query = null, $return_type = RETURN_OBJECTS)
	{

		// populate the data objects
		$query_array = $this->generate_query($sh, $qb, $c_query);
		
		if ($return_type == RETURN_QUERY)
		{
			return $query_array['query'];
		}
		
		$this->get_data_counts($sh, $query_array);
		
		if ($sh instanceof SearchHandler)
		{
			$sh->save();
		}
		
		if ($return_type == RETURN_COUNTS)
		{
			return $query_array['c_query'];
		}
		
		$this->get_data($query_array);
		
		if ($return_type == RETURN_ROWS)
		{
			return $this->data;
		}
		
		//no need to do anything else if there aren't any rows!
		if ($this->num_records > 0)
		{
			$this->build_data_objects();
		}
		
		return true;
		
	}

	// TODO: Change this to protected and amend anything external to the class
	// to call load with type RETURN_QUERY
	public function generate_query(&$sh, &$qb = null, &$c_query = null)
	{
		
		if ($qb === NULL)
		{
			$db = DB::Instance();
			$qb = new QueryBuilder($db, $this->_templateobject);
		}
		
		if ($sh instanceof SearchHandler)
		{
			
			$this->_fields			= $sh->fields;
			$this->display_fields	= $sh->fields;
			
			if (!empty($this->_fields) && empty($sh->groupby))
			{
				$this->addAuditFields($this->_fields);
			}
			
			$sh->addPolicyConstraints($this->addSystemRules($sh));
			
			$query = $this->buildQuery($sh,$qb);
			
			if (isset($sh->groupby))
			{
				$qb->setDistinct();
			}
			
			$this->query = $query;
			
			$query_array['query']	= $query;
			
			if (!empty($c_query))
			{
				$query_array['c_query']	= $c_query;
			}
			else
			{
				$query_array['c_query']	= $qb->countQuery();
			}

			$query_array['perpage']	= $sh->perpage;
			$query_array['c_page']	= $sh->page;
			
		}
		else
		{

			$query_array = array(
				'query'		=> $sh,
				'perpage'	=> 0,
				'c_page'	=> 1,
				'c_query'	=> $c_query	
			);
		
		}
		
		return $query_array;
	
	}
	
	protected function get_data_counts($sh, $query_array)
	{
		
		$db = DB::Instance();
		
		// Cache the record count, but only for paged displays
		if ($query_array['perpage'] !== null && get_config('USE_ADODB_CACHE')) {
			$num_records = $db->cacheGetOne(300, $query_array['c_query']);
			// Remove the cached result if the last page is within one or zero rows of full length.
			// Ensures that a new page is displayed if a record is added.
			if (($num_records !== 0) && ($query_array['perpage'] - ($num_records % $query_array['perpage']) <= 1)) {
				$db->cacheFlush($query_array['c_query']);
			}
		} else {
			$num_records = $db->GetOne($query_array['c_query']);
		}
		
		debug('DataObjectCollection(' . $this->_doname . ')::_load : ' . $query_array['c_query']);	
		debug('DataObjectCollection(' . $this->_doname . ')::_load : No of Records=' . $num_records);
		
		if ($num_records === false)
		{
			throw new Exception($query_array['c_query'] . ' : ' . $db->ErrorMsg());
		}
		
		$this->total_records = $num_records;
		
		if ($sh instanceof SearchHandler && !empty($sh->maxlimit) && $num_records > $sh->maxlimit)
		{
			$num_records = $sh->maxlimit;
		}
		
		$this->num_records = $num_records;

		if ($query_array['perpage'] == 0)
		{
			$this->num_pages = 1;
		}
		else
		{
			$this->num_pages = ceil($num_records / $query_array['perpage']);
		}
		
		if ($sh instanceof SearchHandler)
		{
			$sh->lastpage = $this->num_pages;
		}
		
		$this->cur_page = $query_array['c_page'];
		
	}
	
	protected function get_data($query_array)
	{
		
		$db = DB::Instance();
		
		debug('DataObjectCollection('.$this->_doname.')::_load : '.$query_array['query']);
//		echo 'DataObjectCollection('.$this->_doname.')::_load : '.$query_array['query'].'<br>';
		
		$rows = $db->GetAssoc($query_array['query'], false, true);
		
		if ($rows === false)
		{
			throw new Exception("DataObjectCollection('.$this->_doname.') load failed: ".$query_array['query'].' '.$db->ErrorMsg());
		}
		
		$this->data = array();
		
		foreach ($rows as $id => $row)
		{
			
			$row[$this->_templateobject->idField] = $id;
			
			$this->data[] = $row;
			
		}
		
	}
	
	protected function build_data_objects()
	{
	
		if (is_array($this->data))
		{
			
			foreach ($this->data as $id => $row)
			{
				
				// clone object and set row and data values
				$do					= clone $this->_templateobject;
				$do->_data			= $row;
				
				$do->load($id);
				
				// no need to do a clone (or copy here), as we're handling that above
				$this->_dataobjects[]	= $do;
				
				// just to be sure
				unset($do);
				
			}
				
		}
		
	}
	
	function load($sh, $c_query = null, $return_type = RETURN_OBJECTS)
	{
		
		if (!$this->_valid)
		{
			return false;
		}
		
		$db = DB::Instance();
		$qb = new QueryBuilder($db, $this->_templateobject);
		
		if ($sh instanceof SearchHandler)
		{
			
//			if ($this->usercontrolled) {
//				$cc = new ConstraintChain();
//				$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
//				$sh->addConstraintChain($cc);
//			}

			if ($this->_templateobject->isAccessControlled())
			{
				
 				if (isModuleAdmin())
 				{
 					$qb->setDistinct();
				}
				else
				{

					$sh->addConstraintChain($this->_templateobject->getAccessConstraint('read'));
					$qb->setDistinct();
					
				}
				
			}
			
			$this->sh = $sh;
			
		}
		
		return $this->_load($sh, $qb, $c_query, $return_type);
		
	}

	function getContents($index = null)
	{
		
		if ($index === null)
		{
			return $this->_dataobjects;
		}
		
		if (!isset($this->_dataobjects[$index]))
		{
			return false;
		}
		
		return $this->_dataobjects[$index];

	}

	function delete($sh, $c_query = null)
	{
		
		$db = DB::Instance();
		$qb = new QueryBuilder($db, $this->_templateobject);
		
		if ($sh instanceof SearchHandler)
		{
			
			// Collection may be based on a view so need to get the base table
			// of the DataObject model associated with this DataObjectCollection
			
			$do = new $this->_doname;
			$this->_tablename = $do->getTableName();

			//$this->_tablename = $this->getModel()->getTableName();
			
			if ($this->_templateobject->isAccessControlled())
			{
				
 				if (isModuleAdmin())
 				{
 					$qb->setDistinct();
				}
				else
				{
					$sh->addConstraintChain($this->_templateobject->getAccessConstraint('write'));
				}
				
			}
			
			$this->sh = $sh;
			
		}
		
		return $this->_delete($sh, $qb, $c_query);
		
	}
	
	protected function _delete($sh, $qb, $c_query = null)
	{
		
		$db = DB::Instance();
		
		if ($sh instanceof SearchHandler)
		{
			$query			= $qb->delete()->from($this->_tablename)->where($sh->constraints);
			$this->query	= $query;
			$c_query		= 'SELECT count(*) from ' . $this->_tablename . ' WHERE ' . $sh->constraints->__toString();
		}
		else
		{
			$query = $sh;
		}

		debug('DataObjectCollection(' . $this->_doname . ')::_delete : ' . $c_query);
//		echo 'DataObjectCollection('.$this->_doname.')::_delete : '.$c_query.'<br>';
		debug('DataObjectCollection(' . $this->_doname . ')::_delete : ' . $query);
//		echo 'DataObjectCollection('.$this->_doname.')::_delete : '.$query.'<br>';

		$count = $db->GetOne($c_query);
		
		if ($count == 0)
		{
			// No rows will be deleted so exit here!
			return $count;
		}
		
		$db->StartTrans();
		
		$rows = $db->execute($query);
		
		if ($rows === false)
		{
			throw new Exception("DataObjectCollection('.$this->_doname.') delete failed: ".$query.$db->ErrorMsg());
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		if ($sh instanceof SearchHandler)
		{
			$sh->save();
		}
		
		$deleted = $db->Affected_Rows();
		
		if ($deleted === false)
		{
			// $db->Affected_Rows() not supported by this database
			$deleted = $count;
		}

		debug('DataObjectCollection(' . $this->_doname . ')::_delete : expected=' . $count . ' actual=' . $deleted);
///	echo 'DataObjectCollection('.$this->_doname.')::_delete : expected='.$count.' actual='.$deleted.'<br>';
		
		if ($count != $deleted)
		{
			// Expected delete count does not equal actual deleted count
			return false;
		}
		
		return $deleted;
				
	}

	/*
     * Post function should be used to post variables from a
     * post array and to generate a collection.
     *
     * @todo Change the name of this function
	 */
	static function Factory($post, &$errors = array(), $modelName)
	{

		$collection = new $modelName();
		
		// if the array is grouped by field then flip it around, else leave it as it is
		if (!is_numeric(key($post)))
		{
			$rows = $collection->joinArray($post);
		}
		else
		{
			$rows = $post;
		}
		
		if (empty($rows))
		{
			return false;
		}
		
		$doname = get_class($collection->getModel());
		
		foreach ($rows as $id => $row)
		{

			$model = call_user_func(array($doname, "Factory"), $row, $errors, $doname);
			
			if (is_a($model, $doname))
			{	
				$collection->_dataobjects[] = $model;
			}
			else
			{
				return false;
			}
			
		}

		return $collection;
		
	}

	function save()
	{
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$fail = false;
		
		foreach ($this->_dataobjects as $ob)
		{
			
			if (!$ob->save())
			{
				$fail = true;
			}
			
		}

		if ($fail)
		{
			$db->FailTrans();
			return false;
		}
		
		$db->CompleteTrans();
		
		return true;
		
	}

	function getHeadings()
	{
		
		foreach ($this->display_fields as $fieldname => $tag)
		{
			
			if ($fieldname == $this->idField && !($fieldname == 'username'))
			{
				continue;
			}
			
			$this->headings[$fieldname] = $tag->tag;
			
		}
		
		return $this->headings;
		
	}

	function getFields()
	{
		return $this->_fields;
	}
	
	/*
	 * Copy function
	 *
	 * Used to clone an object before storing it in an array.
	 * This is a bit of a fix because of the limitations of clone
     * P.B.
	 */
	function copy($do)
	{
		return unserialize(serialize($do));
	}

	/*
	 *Join Array Function
     *
     * Designed to take multiple arrays from post and concatinate them into
     * a nice array. eg. $x[1,2,3], $y[4,5,6] => $z[key[1,4], key[2,5], key[3,6]
     *
	 * I did a test with 10000 * 10^3 integers and it took > 0.07 seconds
     * So probably not much of speed concern
     * P.B.
	 */
	static function joinArray($post, $start = 0)
	{
		$count = 0;

		foreach ($post as $key => $array)
		{
			
			if (is_array($array))
			{
				$arrays[$key]	= $array;
				$count			= max($count, max(array_keys($array)) + 1);
			}
			
		}
		
		if ($count < 1)
		{
			return false;
		}

		for ($x = $start; $x < $count + $start; $x++)
		{
			
			$nothing_set = true;
			
			foreach ($arrays as $key => $array)
			{
				
				if (isset($array[$x]))
				{
					$nothing_set		= false;
					$result[$x][$key]	= $array[$x];
				}
				else
				{
					$result[$x][$key] = '';
				}
			}
			
			if ($nothing_set)
			{
				unset($result[$x]);
			}
			
		}

		return $result;

	}

	function setParams()
	{
		
		if (isset($_GET['page'])) $this->page = $_GET['page'];
		if (isset($_GET['limit'])) $this->limit = $_GET['limit'];
		if ($this->limit > 50) $this->limit = 50;
		if (isset($_GET['direction'])) $this->direction = $_GET['direction'];
		if (isset($_GET['orderby'])) $this->orderby = $_GET['orderby'];
		if (isset($_GET['search'])) $this->search = $_GET['search'];
		if (isset($_GET['field'])) $this->searchField = $_GET['field'];

		if ($this->search != null && count($this->search) !== 0 && count($this->searchField) !== 0)
		{
			
			$pointer = 0;
			
			foreach ($this->search as $s)
			{
				$this->searchString .= ' LOWER(' . $this->searchField[0] . ') LIKE ' . "'" . strtolower($s) . "%' AND";
				$pointer++;
			}
			
 			$this->searchString = substr($this->searchString, 0, (strlen($this->searchString) - 3));
			$this->searchString = " WHERE" . $this->searchString;
			
		}
		
		$this->offset = (($this->page * $this->limit) - $this->limit);

	}

	public function getAssoc($field = null)
	{
		
		$result		= array();
		$contents	= $this->getContents();
		
		if (!$contents || empty($contents))
		{
			return array();
		}
		
		$q = $this->query;
		
		debug('DataObjectCollection(' . $this->_doname . ')::getAssoc : ' . $q);
//		echo 'DataObjectCollection(' . $this->_doname . ')::getAssoc : ' . $q.'<br>';
		
		if (!empty($q))
		{
				
			$db			= DB::Instance();
			$t_result	= $db->GetAssoc($q, false, true);
			
			foreach ($t_result as $id => $data)
			{
				
				if ($field !== null)
				{
					$result[$id] = $data[$field];
				}
				else
				{
					
					if (isset($this->_identifierField))
					{
						
						$exploded	= explode('||', $this->_identifierField);
						$return		= '';
						
						foreach ($exploded as $var)
						{
							
							if (isset($data[trim($var)]))
							{
								$return .= $data[trim($var)];
							}
							else
							{
								$return .= str_replace('\'', '', $var);
							}
							
						}
						
						$result[$id] = $return;
						
					}
					else
					{
						$result[$id] = current($data);
					}
					
				}
				
			}
			
		}
		else
		{
			
			foreach ($contents as $model)
			{
				$result[$model->{$model->idField}] = $model->{$model->getIdentifier()};
			}
			
		}
		
		return $result;
		
	}
	
	/**
	 * @param $doc1 DataObjectCollection
	 *[ @param $doc2 DataObjectCollection ]
	 *
	 * Merges 2 DataObjectCollections and returns the Union on the items
	 */
	public static function Merge($doc1, $doc2 = null)
	{
		
		if ($doc2 == null)
		{
			return $doc1;
		}
		
		foreach ($doc2 as $item)
		{
			
			if (!$doc1->find($item))
			{
				$doc1->add($item);
			}
			
		}
		
		return $doc1;
		
	}
	
	public function find($needle)
	{
		
		foreach ($this->_dataobjects as $do)
		{
			
			if ($needle->{$needle->idField} == $do->{$do->idField})
			{
				return true;
			}
			
		}
		
		return false;
		
	}
	
	public function toJSON()
	{

		$array = array();
		
		foreach ($this->getContents() as $item)
		{
			$array[] = $item->toArray();
		}
		
		if (function_exists('json_encode'))
		{
			return json_encode($array);
		}
		
	}
	
	public function getModel()
	{
		return $this->_templateobject;
	}
	
	public function getModelName()
	{
		return $this->_doname;
	}
	
	public function getTableName()
	{
		return $this->_tablename;
	}

	public function getTitle()
	{
		return $this->title;
	}
	
	public function setViewName($tablename)
	{
		$this->setTableName($tablename);
	}
	
	public function setTableName($tablename)
	{
		$this->_tablename = $tablename;
		$this->_templateobject->setViewName($tablename);
		$this->_templateobject->setFields($tablename);
	}
	
	public function setTitle($title = '')
	{
		$this->title = $title;
	}
	
	public function isEmpty()
	{
		return (count($this->_dataobjects) == 0);
	}
	
	public function contains($key, $val)
	{
		
		foreach ($this->getContents() as $index => $model)
		{
			
			if ($model->$key == $val)
			{
				return $index;
			}
			
		}
		
		return false;
		
	}

	
	 //*******************
	// ITERATOR FUNCTIONS
	
	public function current()
	{
		return $this->_dataobjects[$this->_pointer];
	}

	public function next()
	{
		$this->_pointer++;
	}

	public function key()
	{
		return $this->_pointer;
	}

	public function rewind()
	{
		$this->_pointer = 0;
	}

	public function valid()
	{
		return ($this->_pointer<count($this));
	}
	
	// end of iterator
	
	// to implement countable
	public function count()
	{
		return count($this->_dataobjects);
	}
	
	function remove($index)
	{
 		unset($this->_dataobjects[$index]);
		$this->_dataobjects = array_values($this->_dataobjects);
 	}
	
	// implement keyed read
	public function seek($value, $key = '')
	{
		
		if (empty($key))
		{
			$do		= DataObjectFactory::Factory($this->_doname);
			$key	= $do->idField;
		}
		
		$index = $this->contains($key, $value);
		
		if ($index !== false)
		{
			return $this->getContents($index);
		}
		else
		{
			return false;
		}
		
	}

	public function clear()
	{
		unset($this->_dataobjects);
		unset($this->data);
	}

	function update($fields, $values, $sh, $c_query = null)
	{
		
		if (!is_array($fields))
		{
			$fields = array($fields);
		}
		
		if (!is_array($values))
		{
			$values = array($values);
		}
		
		$db = DB::Instance();
		$qb = new QueryBuilder($db, $this->_templateobject);
		
		// Collection may be based on a view so need to get the base table
		// of the DataObject model associated with this DataObjectCollection
		
		$this->_tablename	= $this->getModel()->getTableName();
		$table_columns		= $db->MetaColumnNames($this->_tablename);
		
		if (in_array('lastupdated', $table_columns) && !in_array('lastupdated', $fields))
		{
			$fields[] = 'lastupdated';
			$values[] = 'now()';
		}
		
		if (in_array('alteredby', $table_columns) && !in_array('alteredby', $fields))
		{
			$fields[] = 'alteredby';
			$values[] = EGS_USERNAME;
		}
		
		if ($sh instanceof SearchHandler)
		{
			
			if ($this->_templateobject->isAccessControlled())
			{
				
 				if (isModuleAdmin())
 				{
 					$qb->setDistinct();
				}
				else
				{
					$sh->addConstraintChain($this->_templateobject->getAccessConstraint('write'));
				}
				
			}
			
			$this->sh = $sh;
			
		}
		
		return $this->_update($fields, $values, $sh, $qb, $c_query);
		
	}
	
	protected function _update($fields, $values, $sh, $qb, $c_query=null)
	{
		
		$db = DB::Instance();
		
		if ($sh instanceof SearchHandler)
		{
			
			$query = $qb
				->update($this->_tablename)
				->update_fields($fields, $values)
				->where($sh->constraints);
				
			$this->query	= $query;
			$c_query		= 'SELECT count(*) from ' . $this->_tablename . ' WHERE ' . $sh->constraints->__toString();
			
		}
		else
		{
			$query = $sh;
		}

		debug('DataObjectCollection('.$this->_doname.')::_update : '.$query);
//		echo 'DataObjectCollection('.$this->_doname.')::_update : '.$query.'<br>';

		$count = $db->GetOne($c_query);
		
		if ($count == 0)
		{
			// No rows will be updated so exit here!
//			echo 'Expected update count='.$count.' so return here<br>';
			return $count;
		}
		
		$db->StartTrans();
		
		$rows = $db->execute($query);
		
		if ($rows === false)
		{
			$db->FailTrans();
			throw new Exception("DataObjectCollection(' . $this->_doname . ') update failed: " . $query.$db->ErrorMsg());
		}
		
		$db->CompleteTrans();
		
		if ($sh instanceof SearchHandler)
		{
			$sh->save();
		}
		
		$updated = $db->Affected_Rows();
		
		if ($updated === false)
		{
			// $db->Affected_Rows() not supported by this database
			$updated = $count;
			
		}
		if ($count != $updated)
		{
			// Expected update count does not equal actual updated count
//			echo 'Expected update count='.$count.' actual updated count='.$updated.'<br>';
			return false;
		}
		
		return $updated;
		
	}

	public function version()
	{
		return $this->version;
	}
	
	public function bulk_insert ($fields, $sh, $c_query = null)
	{
		
		$db = DB::Instance();
		$qb = new QueryBuilder($db, $sh->collection);
		
		if ($sh instanceof SearchHandler)
		{
			
			$this->_fields			=$sh->fields;
			$this->display_fields	=$sh->fields;
			
//			if (!empty($this->_fields) && empty($sh->groupby)) {
//				$this->addAuditFields($this->_fields);
//			}

			$this->_tablename	= $sh->collection->getViewName();
			$query				= $this->buildQuery($sh, $qb);
			
			if (isset($sh->groupby))
			{
				$qb->setDistinct();
			}
			
			$this->query	= $query;
			$c_query		= $qb->countQuery($sh->collection->getModel()->identifierField);
			
		}
		else
		{
			$query = $sh;
		}
		
//		echo 'DataObjectCollection('.$this->_doname.')::bulk_insert : '.$c_query.'<br>';		
		debug('DataObjectCollection('.$this->_doname.')::bulk_insert : '.$c_query);	
			
		$num_records = $db->GetOne($c_query);
		
//		echo 'DataObjectCollection('.$this->_doname.')::bulk_insert : No of Records='.$num_records.'<br>';
		debug('DataObjectCollection('.$this->_doname.')::bulk_insert : No of Records='.$num_records);
		
		if ($num_records === false)
		{
			throw new Exception($db->ErrorMsg());
		}
		
		$this->_tablename	= $this->getModel()->getTableName();
		$insert				= 'insert into ' . $this->_tablename . ' (' . implode(',', $fields) . ') ';
		$query				= $insert . $query;

//		echo 'DataObjectCollection('.$this->_doname.')::bulk_insert : '.$query.'<br>';		
		debug('DataObjectCollection('.$this->_doname.')::bulk_insert : '.$query);		
		
		$db->StartTrans();

		$rows = $db->execute($query);
		
		if ($rows === false)
		{
			$db->FailTrans();
			throw new Exception("DataObjectCollection(' . $this->_doname . ') update failed: " . $query . $db->ErrorMsg());
		}
		
		$db->CompleteTrans();
		
		$updated = $db->Affected_Rows();
		
		if ($updated === false)
		{
			// $db->Affected_Rows() not supported by this database
			$updated = $num_records;
		}
		
//		echo 'DataObjectCollection('.$this->_doname.')::bulk_insert : Rows Inserted '.$updated.'<br>';		
		debug('DataObjectCollection('.$this->_doname.')::bulk_insert : Rows Inserted '.$updated);		
		
		if ($num_records != $updated)
		{
			// Expected update count does not equal actual updated count
			return false;
		}
		
		return $updated;
	
	}
	
}

// end of DataObjectCollection.php
