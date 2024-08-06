<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkschedulenotesController extends Controller
{

	protected $version = '$Revision: 1.2 $';

	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('WorkScheduleNote');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'edit');

		$s_data = null;

		$flash = Flash::Instance();

		$errors = array();

		if(isset($this->_data['Search']))
		{
			$s_data = $this->_data['Search'];
		}

		$this->setSearch('EngineeringSearch', 'workNotes', $s_data);

		if(count($errors)>0)
		{
			$flash->addErrors($errors);
			$this->search->clear();
		}

		parent::index(new WorkScheduleNoteCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array('new'	=> array('tag'	=> 'new_work_schedule_note'
								,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> $this->name
												,'action'		=> 'new'),

				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('no_delete',true);
	}

	public function delete($modelName = null)
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$note = $this->_uses[$this->modeltype];

		$flash = Flash::Instance();

		$flash->addError('Deleting Work Schedule Notes is not allowed');

		sendTo('workschedules', 'view', $this->_modules, array('id'=>$note->work_schedule_id));

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash = Flash::Instance();

		$errors = array();

		if(parent::save($this->modeltype, '', $errors))
		{
			sendTo('workschedules', 'view', $this->_modules, array('id'=>$this->saved_model->work_schedule_id));
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$note = $this->_uses[$this->modeltype];

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll']	= array('tag'	=> 'View Work Schedules'
									  ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> 'workschedules'
														,'action'		=> 'index'
														)
									  );

		$sidebarlist['new']	= array('tag'	=> 'New Work Schedule'
								   ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> 'workschedules'
													,'action'		=> 'new'
													)
									   );

		$sidebar->addList('Actions',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['edit']	= array('tag'	=> 'Edit'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'edit'
														,$note->idField	=> $note->{$note->idField}
														)
									   );

		$sidebar->addList('This Note',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

/* protected functions */
	protected function getPageName($base = null, $action = null) {

		$base = empty($base)?$this->_templateobject->getTitle():$base;

		return parent::getPageName(empty($base)?'work_schedule_notes':$base, $action);

	}

/* private functions */

}

// end of WorkschedulenotesController
