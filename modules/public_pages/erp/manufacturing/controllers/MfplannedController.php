<?php

class MfplannedController extends PrintController
{

	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('MFPlanned');

		$this->uses($this->_templateobject);

	}

	public function index()
	{
		$s_data = array();
		$this->setSearch('mfplannedSearch', 'useDefault', $s_data);
		$this->view->set('clickaction', 'view');
		$plannedorders = new MFPlannedCollection($this->_templateobject);
		parent::index($plannedorders);
		$plannedorders_objects = $plannedorders->getContents();

		$this->view->set('plannedorders', $plannedorders);
	}

}