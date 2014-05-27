<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SelectorCollection extends DataObjectCollection {

	protected $version = '$Revision: 1.14 $';
	
	function __construct($do = 'SelectorObject', $tablename = '')
	{
		parent::__construct($do, $tablename);
	}
	
	public function setOverview()
	{
		return $this->selectorOverview();
	}

	/*
	 * Static Functions
	 */
	
	/*
	 * static function getItemHierarchy($type, $_item_id = '')
	 * 
	 * Constructs a constraint chain from the parent items
	 * 
	 * @param string $type identifier type for the selector
	 * @param string $_item_id item id at which to start
	 * @return constraintchain or null if no item id given
	 */
	static function getItemHierarchy($type, $_item_id = '')
	{
		// Get the type descriptor details
		$typedetails	= SelectorCollection::getTypeDetails($type);
		$item			= new SelectorObject($typedetails['itemTableName']);
	
		if (!empty($_item_id))
		{
			$cc = new ConstraintChain();
			$cc->add(New Constraint('id', '=', $_item_id));
			
		}
		else
		{
			
			return null;
			
		}
		
		// Load the given item
		$item->loadBy($cc);
		$item_cc = new ConstraintChain();
		
		// Now get each parent in turn to construct constraintchain
		while ($item->isLoaded())
		{
			// TODO: added <name>_id as column in view so could change this to
			// $item_cc->add(New Constraint($item->description.'_id', '=', $item->id));
			$item_cc->add(New Constraint($item->description, '=', $item->name));
			
			if (is_null($item->parent_id))
			{
				break;
			}
			$cc = new ConstraintChain();
			$cc->add(New Constraint('id', '=', $item->parent_id));

			$item->loadBy($cc);
			
		}
		
		return $item_cc;
		
	}
	
	/*
	 * static function getItems($type, $target_ids = '')
	 * 
	 * Gets the item ids linked to the given target ids
	 * 
	 * @param string $type identifier type for the selector
	 * @param string $target_ids 
	 * @return array of item_id=>target ids
	 */
	static function getItems($type, $target_ids = '')
	{
		// Get the type descriptor details
		$typedetails		= SelectorCollection::getTypeDetails($type);
		$selectedtargets	= new SelectorCollection(new SelectorObject($typedetails['linkTableName']));
		
		$sh = new SearchHandler($selectedtargets, false);
		
		if (!empty($target_ids))
		{

			if (!is_array($target_ids))
			{
				$target_ids = array($target_ids);
			}
			
			$sh->addConstraint(New Constraint('target_id', 'in', '('.implode(',', $target_ids).')'));
			
		}
		
		$sh->setFields(array('item_id', 'target_id'));
		$sh->setOrderby('target_id');
		return $selectedtargets->load($sh, null, RETURN_ROWS);
		
	}

	/*
	 * static function getTargets($type, $item_ids = '')
	 * 
	 * Gets the target ids linked to the given item ids
	 * 
	 * @param string $type identifier type for the selector
	 * @param string $target_ids 
	 * @return array of target_id=>item_id
	 */
	static function getTargets($type, $item_ids = '')
	{
		// Get the type descriptor details
		$typedetails	= SelectorCollection::getTypeDetails($type);
		$selecteditems	= new SelectorCollection(new SelectorObject($typedetails['linkTableName']));
		
		$sh = new SearchHandler($selecteditems, false);
		
		if (!empty($item_ids))
		{

			if (!is_array($item_ids))
			{
				$item_ids = array($item_ids);
			}
			
			$sh->addConstraint(New Constraint('item_id', 'in', '('.implode(',', $item_ids).')'));
			
		}
		
		$sh->setFields(array('target_id', 'item_id'));
		$sh->setOrderby(array('target_id', 'item_id'));
		$selecteditems->load($sh);
		
		return $selecteditems->getAssoc();
		
	}

	/*
	 * static function getTypeDetails($type)
	 * 
	 * Reads manifest file containing type details in json format
	 * and returns the details matching the given type, if it exists
	 * 
	 * @param string $type identifier type for the selector
	 * @return array of type details, emty array if none found
	 */
	static function getTypeDetails($type)
	{
		
		$manifest = json_decode(file_get_contents(DATA_ROOT . 'company' . EGS_COMPANY_ID . DIRECTORY_SEPARATOR . 'manifest.json'), TRUE);
		
		foreach($manifest as $definition)
		{
			if (isset($definition[$type]))
			{
				return $definition[$type];
			}
			
		}
		
		return array();
		
	}
	
	static function TypeDetailsExist($type)
	{
		
		$manifest = json_decode(file_get_contents(DATA_ROOT . 'company' . EGS_COMPANY_ID . DIRECTORY_SEPARATOR . 'manifest.json'), TRUE);
		
		foreach($manifest as $definition)
		{
			if (isset($definition[$type]))
			{
				return TRUE;
			}
			
		}
		
		return FALSE;
		
	}
	
	/*
	 * static function copyItems($_data = '', &$errors = array())
	 * 
	 * Copies all links for a given from item, to item and table name
	 * 
	 * @param string $_data
	 * @param string $errors
	 * @return bool true on success, false on failure
	 */
	static function copyItems($_data = '', &$errors = array())
	{

		if (empty($_data['tablename']))
		{
			$errors[] = 'No table name provided';
		}
		
		if (empty($_data['from_item_id']))
		{
			$errors[] = 'From item not provided';
		}

		if (empty($_data['to_item_id']))
		{
			$errors[] = 'To item not provided';
		}

		if (count($errors)>0)
		{
			return false;
		}
		
		$do			= new DataObject($_data['tablename']);
		$do->identifierField = 'target_id';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('item_id', '=', $_data['from_item_id']));
		$copyitems = $do->getAll($cc);

		foreach ($copyitems as $target_id)
		{
			
			$link_data[] = array('item_id'		=> $_data['to_item_id']
								,'target_id'	=> $target_id);
		}
		
		return self::saveAssociations($link_data, $_data['tablename'], $errors);
	
	}

	/*
	 * static function saveAssociations($_link_data = array(), $_tablename = '', &$errors = array())
	 * 
	 * Save the item/target links in the given link data array
	 * in the given table name
	 * 
	 * @param string $_link_data
	 * @param string $_tablename
	 * @param string $errors
	 * @return bool true on success, false on failure
	 */
	static function saveAssociations($_link_data = array(), $_tablename = '', &$errors = array())
	{
		
		$models=array();
		$result=true;
		
		foreach ($_link_data as $link)
		{
			$do=new DataObject($_tablename);
			$model=DataObject::Factory($link, $errors, $do);
			if (count($errors)>0 || !$model)
			{
				$errors[]='Error validating selected link';
				$result=false;
				break;
			}
			else
			{
				$models[]=$model;
			}
		}
		
		if ($result && count($models)>0)
		{
			foreach ($models as $model)
			{
				$result=$model->save();
				if (!$result)
				{
					$errors[]='Error saving selected link';
					break;
				}
			}
		}
	
		return $result;
		
	}

	public function selectorLinkOverview($_itemFields, $_linkTableName, $_targetModel, $_targetFields)
	{
		
		$targetModelName = $_targetModel.'Collection';
		$target = new $targetModelName($_targetModel);
		$target_table = $target->getViewName();
		
		$item_table = $this->selectorOverview();
		
		$query = '(select link.id, item_id, target_id, '.implode(',', array_merge($_itemFields, $_targetFields))
				.' from '.$_linkTableName.' link '
				.'    , '.$item_table
				.'    , '.$target_table.' as target '
				.' where target.id = link.target_id'
				.'   and selector_overview.id = link.item_id) as selector_link_overview';
		
		return $query;
		
	}


	
	/*
	 * Private Functions
	 */
	
	/*
	 * private function selectorOverview()
	 * 
	 * Creates a sql query to flatten the selector parent-child relationships
	 * 
	 * @return string sql query
	 */
	private function selectorOverview()
	{
		
		$fields	= array();
		$tables	= array();
		$cc		= new ConstraintChain();
		$count	= 0;
		
		$this->orderby = $this->_templateobject->getDisplayFieldNames();
		
		foreach ($this->_templateobject->getDisplayFieldNames() as $field => $tag)
		{
			
			$count++;
			
			$fields[$field] = 'a' . $count . '.name as ' . $field;
			$fields[$field.'_id'] = 'a' . $count . '.id as ' . $field . '_id';
			$tables[$count] = $this->_tablename . ' a' . $count;
			
			if ($count > 1)
			{
				$cc->add(new Constraint('a' . $count . '.parent_id', '=', '(a' . ($count - 1) . '.id)'));
			}
			
		}
		
		$fields['usercompanyid']	= 'a' . $count . '.usercompanyid';
		
		$fields = array_merge(array('id' => 'a' . $count . '.id')
							, $fields);
		
		$query = '(select '.implode(',', $fields).
				' from '.implode(',', $tables).
				' where '.$constraint=$cc->__toString().
				') as selector_overview';
		
		return $query;
		
	}

}

// end of SelectorCollection.php