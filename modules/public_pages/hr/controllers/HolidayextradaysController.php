<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class HolidayextradaysController extends Controller
{

	protected $version='$Revision: 1.7 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('Holidayextraday');

		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');

		parent::index(new HolidayextradayCollection($this->_templateobject));
	}

	public function delete($modelName = null)
	{
		$flash = Flash::Instance();

		parent::delete($this->modeltype);

		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash=Flash::Instance();
	//$this->_data['Holidayextraday']['authorisedby']=EGS_USERNAME;

		if(parent::save($this->modeltype))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
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

		$holidayExtraDay = $this->_uses[$this->modeltype];

		$entitlement = DataObjectFactory::Factory('Holidayentitlement');
		$entitlement->load($holidayExtraDay->entitlement_period_id);

		$employee = DataObjectFactory::Factory('Employee');
		$employee->load($holidayExtraDay->employee_id);
		$this->view->set('employee',$employee);

		$currently_viewing = $employee->employee . ': ' . $entitlement->start_date . '-' . $entitlement->end_date;

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'currently_viewing',
			array(
				$employee->employee=>array(
					'tag' => $currently_viewing,
					'link' => array('module'=>'hr'
								   ,'controller'=>'holidayentitlements'
								   ,'action'=>'view'
								   ,'id'=>$entitlement->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('module'=>'hr'
								   ,'controller'=>'holidayextradays'
								   ,'action'=>'edit'
								   ,'id'=>$holidayExtraDay->id)
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function edit()
	{
		//If the user is HR Admin
		if(ismoduleadmin())
		{
			parent::edit();
		}
		else
		{
			$flash = Flash::Instance();

			$flash->addError('You do not have permission to edit a Holiday Extra Day!');

			sendTo();
		}
	}

	public function _new()
	{
		if(ismoduleadmin())
		{
			$employee = DataObjectFactory::Factory('Employee');
			$employee->load($this->_data['employee_id']);
			$this->view->set('employee', $employee);

			parent::_new();
		}
		else
		{
			$flash = Flash::Instance();

			$flash->addError('You do not have permission to add a Holiday Extra Day!');

			sendTo();
		}
	}

}

// End of HolidayextradaysController
