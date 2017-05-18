<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class StuomconversionsController extends Controller
{

	protected $version='$Revision: 1.13 $';
	
	protected $_templateobject;

	protected $related;
	
	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new STuomconversion();
		
		$this->uses($this->_templateobject);
		
		$this->related['_stitem']['clickaction'] = 'edit';
		
		$this->related['_stitem']['allow_delete'] = true;
		
	}

	public function index()
	{
		
		parent::index(new STuomconversionCollection($this->_templateobject));
		
		$this->view->set('clickaction', 'edit');
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New UOM Conversion'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  )
												)
							)
				 )
			);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete('STuomconversion');
		
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	
	}

	public function _new()
	{
		
		parent::_new();
		
// For new actions the stitem_id and from_uom_id will be set
// For edit actions, the id will be set pointing to the uom conversion to be edited
		if ( $this->_data['action'] == 'edit')
		{
			$stuom = $this->_uses[$this->modeltype];
			$stitem_id = $stuom->stitem_id;
			$stitem_uom_id = $stuom->from_uom_id;
			$stitem_uom_name = $stuom->from_uom_name;
		}
		else
		{
			$stitem=new STitem();
			$stitem->load($this->_data['stitem_id']);
			$stitem_id = $stitem->id;
			$stitem_uom_id = $stitem->uom_id;
			$stitem_uom_name = $stitem->uom_name;
		}
		
		$this->view->set('stitem_id', $stitem_id);
		$this->view->set('stitem_uom_id', $stitem_uom_id);
		$this->view->set('stitem_uom_name', $stitem_uom_name);
		$this->view->set('stitem', Stuomconversion::getStockItem($stitem_id));
		
		$elements = new STuomconversionCollection(new STuomconversion());
		
		$sh = new SearchHandler($elements, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('stitem_id','=', $stitem_id));
		$sh->setOrderBy('from_uom_name');
		$sh->extractOrdering();
		$sh->extractPaging();
		
		$elements->load($sh);
		
		$this->view->set('elements',$elements);
		$this->view->set('no_ordering',true);
		
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
		
		$stitem=new STItem();
		$stitem->load($this->_data['STuomconversion']['stitem_id']);
		
		if ($stitem->uom_id==$this->_data['STuomconversion']['from_uom_id']
			|| $stitem->uom_id==$this->_data['STuomconversion']['to_uom_id'])
		{
			if(parent::save('STuomconversion'))
			{
				sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
			}
		}
		else
		{
			$flash->addError('The From UoM or the To UoM must be the base UoM');
		}

		$this->_data['stitem_id']=$this->_data['STuomconversion']['stitem_id'];
		$this->refresh();

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('Stock UoM Conversions');
	}

}

// End of StuomconversionsController
