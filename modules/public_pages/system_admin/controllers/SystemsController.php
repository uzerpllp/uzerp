<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystemsController extends Controller {

	protected $version = '$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
	}

	public function index()
	{
		
		global $smarty;
		
		if (get_config('SETUP'))
		{
			
			if (empty(get_config('DB_TYPE')))
			{
				$this->newSchema();
				$this->_templateName = $this->getTemplateName('newschema');
			}
			
		}
		
		$this->view->set('clickaction', 'edit');
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete('System');
		
		sendTo($this->name, 'index', $this->_modules);
		
	}
	
	public function save()
	{
		
		$flash = Flash::Instance();
		
		if(parent::save('System'))
		{
			sendTo($this->name, 'index', $this->_modules);
		}
		else
		{
			$this->_new();
			$this->_templateName = $this->getTemplateName('new');
		}

	}
	
	public function extractSchema()
	{
		
		$db			= DB::Instance();
		$xmlschema	= $db->extractSchema();
		
		echo $xmlschema;
		
	}

	public function newSchema()
	{
		
		$schema = new Schema();
		
		$this->view->set('Schema', $schema);
		$this->view->set('databases', $schema->supportedDatabases());
		$this->view->set('page_title', $this->getPageName('Database', 'Create New'));
		
	}
	
	public function createSchema()
	{

		$data = $this->_data;
		
		if (isset($data['cancel']))
		{
			$this->view->set('info_message', 'Database creation cancelled');
			sendTo($this->name, 'index', $this->_modules);
		}
		
		if (isset($data['createdb']) && isset($data['Schema']))
		{
			
			set_config('DB_TYPE', $data['Schema']['database_type']);
			set_config('DB_USER', $data['Schema']['database_admin_username']);
			set_config('DB_HOST', $data['Schema']['database_host']);
			set_config('DB_PASSWORD', $data['Schema']['database_admin_password']);
			set_config('DB_CREATE', true);
			
			$db = DB::Instance();
			
			if ($db === null)
			{
				echo 'Unable to connect to database<br>';
			}
			
			$dict	= NewDataDictionary($db);
			$sql	= $dict->CreateDatabase($data['Schema']['database_name']);
			$result	= $dict->ExecuteSQLArray($sql);
			
			if ($result != 2)
			{
				$this->view->set('message', $db->ErrorMsg());
				$this->setTemplateName('systemerror');
				$this->view->set('page_title', $this->getPageName('Creation Error', 'Database'));
			}
			else
			{
				$this->view->set('message', 'Database created');
				$this->setTemplateName('systemerror');
				$this->view->set('page_title', $this->getPageName('Created', 'Database'));
			}
			
		}
		
	}

	public function createTable()
	{

		$db	= DB::Instance();
		
		$tablename	= 'system_test';
		$fields		= "id I8 NOTNULL AUTOINCREMENT PRIMARY,
				 setting_type_id I4 NOTNULL,
				 setting_name C NOTNULL,
				 setting_value C NOTNULL,
				 usercompanyid I8 NOTNULL";
		
		$taboptions = array('constraints'=>',CONSTRAINT system_test_unique_key UNIQUE (setting_type_id, setting_name)
											,CONSTRAINT system_test_setting_type_fkey FOREIGN KEY (setting_type_id) REFERENCES system_types (id) ON UPDATE CASCADE');
		
		$dict			= NewDataDictionary($db);
		$createtable	= $dict->CreateTableSQL($tablename, $fields, $taboptions);
		
		echo '<br>';
		
		foreach ($createtable as $key => $value)
		{
			echo $key . '=' . $value . '<br>';
		}
		
		echo 'Create Table result=' . $dict->ExecuteSQLArray($createtable) . '<br>';
		
	}

	protected function getPageName($base = null, $type = null)
	{
		return parent::getPageName((empty($base) ? 'System' : $base), $type);
	}

}

// end of SystemsController.php