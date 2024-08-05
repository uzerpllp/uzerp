<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportDefinitionsController extends printController
{

	protected $version = '$Revision: 1.5 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = new ReportDefinition();

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors=array();

		$s_data=array();

		$this->setSearch('outputSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'edit');

		$reports = new ReportDefinitionCollection($this->_templateobject);

		parent::index($reports);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['new'] = array('tag'	=> 'New Report Definition'
								   ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'new'
													)
				 			  );

		$sidebar->addList(
			'Actions',
			$sidebarlist
			);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function copy()
	{
		parent::edit();

		$report_definition = $this->_uses[$this->modeltype];

		$report_definition->setTitle($report_definition->getTitle().' '.$report_definition->name);

		$report_definition->id				= null;
		$report_definition->name			= $report_definition->name.'_copy';
		$report_definition->user_defined	= TRUE;

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['action'] = array('tag'	=> 'View All Report Definitions'
									  ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'index'
														)
				 			  );

		$sidebarlist['new'] = array('tag'	=> 'New Report Definition'
								   ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'new'
													)
				 			  );

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function edit()
	{
		parent::edit();

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['action'] = array('tag'	=> 'View All Report Definitions'
									  ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'index'
														)
				 			  );

		$sidebarlist['new'] = array('tag'	=> 'New Report Definition'
								   ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'new'
													)
				 			  );

		$sidebarlist['copy'] = array('tag'	=> 'Copy Report Definition'
									,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'copy'
													,'id'			=> $this->_data['id']
													)
				 			  );

		$sidebar->addList('Actions',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['report'] = array('tag'	=> 'Print Test Report'
									  ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'printDialog'
														,'printaction'	=> 'testReport'
														,'id'			=> $this->_data['id']
														)
				 			  );

		$sidebar->addList('Reports',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		$flash=Flash::Instance();

		$errors=array();

		if(parent::save('ReportDefinition','',$errors))
		{
			sendBack();	
		}
		else
		{
			$flash->addErrors($errors);
			sendBack();
		}
	}

	public function testReport($status='generate')
	{

		#dbug::r($this->_data,'',true);

		$report_definition = $this->_templateobject;

		$report_definition->load($this->_data['id']);

		#dbug::r($report_definition->test_xml,'',true);exit;

		// build options array
		$options=array('type'		=>	array('pdf'=>'',
											  'xml'=>''
										),
					   'output'		=>	array('print'=>'',
					   						  'save'=>'',
					   						  'email'=>'',
					   						  'view'=>''
										),
					   'filename'	=>	'testing report '.$report_definition->name,
					   'report'		=>	$report_definition->name
				);


		switch(strtolower((string) $status))
		{
			case "dialog":
			default:
				// show the main dialog
				// pick up the options from above, use these to shape the dialog
				return $options;
				break;

			case "generate":
				// generate the xml and add it to the options array
				$options['xmlSource']=$report_definition->test_xml;
				// execute the print output function, echo the returned json for jquery
				echo $this->constructOutput($this->_data['print'],$options);
				exit;
				break;
		}

		exit;
	}


	/* this will need a bit of testing */
	/* if we wanted to do a bit of post processing, let's do it now! */
	public function getDefinition($name)
	{
		$report_definition=$this->_templateobject;

		$report_definition->load($name);

		return $report_definition->definition;
	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('report definitions');
	}

}

// End of 
