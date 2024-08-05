<?php

class UzletsController extends printController {

	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('Uzlet');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query=null)
	{

		$errors = array();

		$s_data = array();

// Set context from calling module
		$this->setSearch('UzletSearch', 'Uzlets', $s_data);

		$this->view->set('clickaction', 'edit');

		$uzlets = new UzletCollection($this->_uses[$this->modeltype]);

		parent::index($uzlets);

		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();

		$sidebarlist['new']=array('tag'=>'New uzLet'
							  ,'link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'new'
											)
				 			  );

		$sidebar->addList(
			'Actions',
			$sidebarlist
			);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();

		// get the uzlet object
		$uzlet = $this->_uses[$this->modeltype];

		// get uzlet calls
		$this->view->set('uzlet_calls', $uzlet->getCalls());

		// get uzlet modules
		$this->view->set('selected_uzlet_modules', $uzlet->getModules());

		// get all modules
		$modules = DataObjectFactory::Factory('ModuleObject');

		$this->view->set('uzlet_modules', $modules->getAll());

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['index'] = array('tag'=>'View All Uzlet'
									 ,'link'=>array('modules'		=> $this->_modules
												   ,'controller'	=> $this->name
												   ,'action'		=> 'index'
												   )
				 			  );
		$sidebarlist['new'] = array('tag'=>'New Uzlet'
								   ,'link'=>array('modules'=>$this->_modules
												 ,'controller'=>$this->name
												 ,'action'=>'new'
												 )
				 			  );

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function save($modelName = null, $dataIn = array(), &$errors = array())
	{
		$flash = Flash::Instance();

		$errors = array();

		// delete any existing uzlet / module relationships
		if(isset($this->_data['Uzlet']['id']) && !empty($this->_data['Uzlet']['id']))
		{
			$uzlet_modules = new UzletModuleCollection(DataObjectFactory::Factory('UzletModule'));

			$sh=new SearchHandler($uzlet_modules, false);

			$sh->addConstraint(new Constraint('uzlet_id', '=', $this->_data['Uzlet']['id']));

			$uzlet_modules->delete($sh);
		}

		// apply uzlet_id to UzletModulesCollection
		if (isset($this->_data['UzletModuleCollection']) && !empty($this->_data['UzletModuleCollection']['module_id']))
		{
			foreach ($this->_data['UzletModuleCollection']['module_id'] as $key=>$value)
			{
				$this->_data['UzletModuleCollection']['uzlet_id'][$key] = '';
			}
		}

		// delete any existing calls
		if(isset($this->_data['Uzlet']['id']) && !empty($this->_data['Uzlet']['id']))
		{
			$uzlet_calls = new UzletCallCollection(DataObjectFactory::Factory('UzletCall'));

			$sh=new SearchHandler($uzlet_calls, false);

			$sh->addConstraint(new Constraint('uzlet_id', '=', $this->_data['Uzlet']['id']));

			$uzlet_calls->delete($sh);
		}

		// prepare the call field
		if (isset($this->_data['UzletCallCollection']['Call']) && $this->_data['UzletCallCollection']['Call']!='')
		{

			// split up the call field by line
			$arr = explode("\n",(string) $this->_data['UzletCallCollection']['Call']);

			foreach($arr as $key=>$value)
			{

				// we're not interested in empty lines
				$empty_test = trim($value);

				if(!empty($empty_test))
				{
					$pieces = explode(":", $value);
					// we're also only interested if we have two pieces to work with
					// actually we're interested if we have MORE THAN 2 parts, the arguement might be json for example
					if(count($pieces)>=2)
					{
						$this->_data['UzletCallCollection']['uzlet_id'][$key]	= '';
						$this->_data['UzletCallCollection']['func'][$key]		= $pieces[0];
						// remove the function from the array, bind all the remaining part back together
						// this will preserve any parameter that contains JSON etc (well, anything with a ":") 
						unset($pieces[0]);
						$this->_data['UzletCallCollection']['arg'][$key] = implode(':',$pieces);
					}
				}
			}
			unset($this->_data['UzletCallCollection']['Call']);
		}
		else
		{
			unset($this->_data['UzletCallCollection']);
		}

		if(parent::save('Uzlet','',$errors))
		{
			sendBack();	
		}
		else
		{
			$flash->addErrors($errors);
			sendBack();
		}

		return true;
	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName('uzLets');
	}

}

// End of UzletsController
