<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SearchHandler {

	protected $version = '$Revision: 1.28 $';
	
	private $model;
	public $fields = array();
	public $groupby = array();
	public $constraints;
	public $policies = array();
	private $offset;
	private $orderby;
	public $orderdir;
	public $perpage;
	public $maxlimit;
	public $lastpage = 1;
	public $collection;
	private $page;
	private $use_session;
	private $use_system_company;	
	private $search_id;
		
	public function __construct(DataObjectCollection $collection, $use_session = TRUE, $use_system_company = TRUE, $search_id = '')
	{
		
		$tablename					= $collection->getViewName();
		$this->tablename			= $tablename;
		$this->use_session			= $use_session;
		$this->search_id			= $search_id;
		$this->constraints			= new ConstraintChain();
		$this->use_system_company	= $use_system_company;
		
		$this->setOrderby($collection->orderby, $collection->direction);
		
		if (strpos($tablename, ' '))
		{
			$this->tablename = substr($tablename, strrpos($tablename, ' ')+1);
		}
				
		$cache_id = array(
			'searches',
			EGS_USERNAME,
			$this->tablename . '_' . $this->search_id
		);
		
		$cache			= Cache::Instance();		
		$cached_search	= $cache->get($cache_id, 1800);
		
		if ($this->use_session && $cached_search !== FALSE)
		{
			
			foreach ($cached_search as $key => $val)
			{
				
   				if ($key == 'constraints' || $key == 'fields')
   				{
					$this->$key = unserialize($val);
  				}
  				else
  				{
					$this->$key = $val;
  				}
  				
			}
			
			debug('SearchHandler::__construct ' . $tablename . ' ' . print_r($this, TRUE));
//			echo 'SearchHandler::__construct<pre>'.print_r($this, true).'</pre>';

		}
		elseif ($this->use_system_company)
		{
			
			//if usercompanyid is a field, then it's always a constraint
			// TODO: Need to revisit this;
			//       gets added in DataObject(?)/DataObjectCollection(?)
			//       if the search is access controlled
			
			$model = $collection->getModel();
			
			if($model->isField('usercompanyid'))
			{
				$this->constraints = new ConstraintChain();
				$this->addConstraint(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
			}
			
		}
		
		$this->collection = $collection;
		
	}

	public function save() {
		
		$this->constraints->removeByField('usercompanyid');
		
		$array = array();
		
		$array['fields']		= serialize($this->fields);
		$array['constraints']	= serialize($this->constraints);
		$array['orderby']		= $this->orderby;
		$array['orderdir']		= $this->orderdir;
		$array['perpage']		= $this->perpage;
		$array['page']			= $this->page;
		$array['maxlimit']		= $this->maxlimit;
		$array['lastpage']		= $this->lastpage;
		$array['lastupdated']	= time();
		
		// instanciate and set the cache
		$cache		= Cache::Instance();
		$cache_id	= array(
			'searches',
			EGS_USERNAME,
			$this->tablename . '_' . $this->search_id
		);
		
		$cache->add($cache_id, $array, 1800);
				
		debug('SearchHandler::save '.$this->tablename.' '.print_r($array, TRUE));
		
	}

	public function __get($var) {
		return $this->$var;
	}

	public function extract() {
		$this->extractFields();
		$this->extractConstraints();
		$this->extractOrdering();
		$this->extractPaging();
	}

	public function extractPaging($page='', $perpage='') {
		if (!empty($perpage)) {
			$this->perpage=$perpage;
		} elseif(isset($_GET['perpage'])) {
			$this->perpage=intval($_GET['perpage']);
		} elseif(defined('EGS_USERNAME')) {
			$userPreferences = UserPreferences::instance(EGS_USERNAME);
			$this->perpage=$userPreferences->getPreferenceValue('items-per-page','shared');
		} else {
			$this->perpage=10;
		}
		if (!is_numeric($this->perpage)) {
			$this->perpage = 10;
		}
		
		if (!empty($page)) {
			$this->page=$page;
		} elseif(isset($_GET['page'])) {
			$this->page=intval($_GET['page']);
		} else {
			$this->page=1;
		}
		if (!is_numeric($this->page)) {
			$this->page = 1;
		}
		
		if ($this->page>$this->lastpage) {
			$this->page=$this->lastpage;
		} elseif ($this->page<1){
			$this->page=1;
		}
		$this->offset=($this->page-1)*$this->perpage;
	}

	public function extractFields() {
		//the model has the default fields, so lets get them
		$model = $this->collection->getModel();
		$fields = $this->collection->getFields();
		foreach ($fields as $field=>$tag) {
			if ($model->isField($field.'_id'))
				$fields[$field.'_id'] = $model->getField($field.'_id');
		}
		//and we want 'id' as well
		if (!isset($fields[$model->idField])) {
			$fields=array_merge(array($model->idField=>$model->getField($model->idField))
								, $fields);
		}
		$this->fields=$fields;
	}
	
	public function setFields($fields) {
		$this->fields=array();
		if($fields=='*') {
			$this->fields = $this->collection->getModel()->getFields();
 		} else {
 			if (!is_array($fields)) {
	 			$fields=array($fields);
			}
			foreach($fields as $field) {
				if($field instanceof DataField) {
					$this->fields[$field->name]=$field;
				} else {
					$tmp=strrpos(strtolower($field), ' as ');
					if ($tmp) {
						$fieldname=substr($field, $tmp+4);
					} else {
						$fieldname=$field;
					}
					$this->fields[$fieldname]=new DataField($field);
					$this->fields[$fieldname]->name=$field;
					$this->fields[$fieldname]->tag=prettify($fieldname);
				}
			}
 		}
	}
	
	public function setGroupBy($groupby) {
		$this->groupby=$groupby;
	}
	
	private function extractConstraints() {
		//check for 'active' searches
		if(isset($_REQUEST['clearsearch']))
		{
			$this->constraints = new ConstraintChain();
		}
		if(isset($_POST['search'])) {
			foreach($_POST['search'] as $fieldname=>$search) {
				$this->constraints[$fieldname][]=ConstraintFactory::Factory($this->model->getField($fieldname),$search);
			}
		}
		if(isset($_POST['quicksearch']) && isset($_POST['quicksearchfield']))
		{
			if($_POST['submit']=='Go')
			{
				$this->constraints = new ConstraintChain();
			}
			$model = new $this->collection->_doname;
			$searchfield = strtolower($_POST['quicksearchfield']);
			$search = '%'.strtolower($_POST['quicksearch']).'%';
			$cc = new ConstraintChain();
			if ($model->getField($searchfield)->type == 'bool') {
				switch(strtolower($_POST['quicksearch'])) {
					case "yes":
					case "true":
					case "y":
					case "t":
						$cc->add(new Constraint($searchfield,'=','true'));
						break;
					default:
						$cc->add(new Constraint($searchfield,'=','false'));
				}
			}
			else
				$cc->add(new Constraint('lower('.$searchfield.')','LIKE',$search, 'user'));
			$this->addConstraintChain($cc);		
		}

		//clearing the search will revert to a save, if one exists
		if($this->use_session&&isset($_POST['clearsearch'])) {
			if(isset($_SESSION['preferences']['savedsearches'][$this->tablename])) {
				$this->constraints=$_SESSION['preferences']['savedsearches'][$this->tablename];
			}
			else {
				$this->constraints=new ConstraintChain();
			}
		}
		//saving sets the current search to be called upon in the future
		if($this->use_session&&isset($_POST['savesearch'])) {
			$_SESSION['preferences']['savedsearches'][$this->tablename]=$this->constraints;
		}
		if($this->use_session&&count($this->constraints)==0&&isset($_SESSION['preferences']['savedsearches'][$this->tablename]))
			$this->constraints=$_SESSION['preferences']['savedsearches'][$this->tablename];
		//if usercompanyid is a field, then it's always a constraint
		$model=$this->collection->getModel();
		if($model->isField('usercompanyid') && $this->use_system_company) {
			$this->addConstraint(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		}

	}

	public function extractOrdering() {
		$model=$this->collection->getModel();
		if(isset($_GET['orderby'])) {
			$orderby='';
			if (is_array($this->orderby)) {
				if (isset($this->orderby[0])) {
					$orderby=$this->orderby[0];
				}
			} else {
				$orderby=$this->orderby;
			}
			if($_GET['orderby']!=$orderby) {
// Add the Identifier Field to ensure the ordering is unique
// This overcomes postgres database problem whereby offset does
// not return consistent set of rows when paging
				$this->orderby=array($_GET['orderby'], $model->idField);
				$this->orderdir='ASC';
			}
			else {
				$this->orderdir=($this->orderdir=='ASC')?'DESC':'ASC';
			}
		}
		else {
			if($this->orderby==null) {
				$this->orderby=$model->collection->orderby;
				$this->orderdir=$model->collection->direction;
			}
			if (is_array($this->orderby)) {
				if (!in_array($model->idField, $this->orderby)) {
					$this->orderby[]=$model->idField;
				}
			} elseif ($this->orderby!=$model->idField) {
				$this->orderby=array($this->orderby, $model->idField);
			}
			if($this->orderdir==null) {
				$this->orderdir='ASC';
			}
		}
		
	}

	private function checkFields() {
		foreach($this->fields as $fieldname=>$field) {
			if(!$this->model->isField($fieldname))
				$this->model->addField($fieldname, $field);
		}
		return true;
	}

	public function addConstraint($constraint,$type='AND') {
		$this->constraints->add($constraint,$type);
	}

	public function addConstraintChain($cc,$type='AND') {
		debug('SearchHandler::addConstraintChain before '.print_r($this->constraints, true));
		debug('SearchHandler::addConstraintChain adding '.get_class($cc).' '.print_r($cc, true));
		$this->constraints->add($cc,$type);
		debug('SearchHandler::addConstraintChain after '.print_r($this->constraints, true));
	}

	function setOrderby($orderby,$orderdir='ASC') {
		$this->orderby=$orderby;
		$this->orderdir=$orderdir;
	}

	function setLimit($limit,$offset=0) {
		$this->perpage=$limit;
		$this->offset=$offset;
	}
	
	function addPolicyConstraints($_policyConstraint)
	{
		$this->addConstraint($_policyConstraint['constraint']);
		
		$this->policies = $_policyConstraint['name'];
	}

}

// end of SearchHandler.php