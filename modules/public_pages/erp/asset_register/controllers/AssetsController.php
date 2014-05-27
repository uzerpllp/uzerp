<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AssetsController extends printController
{

	protected $version = '$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Asset');
		
		$this->uses($this->_templateobject);
	
	}

	public function index()
	{
		
		$this->view->set('clickaction', 'view');
		
// Search
		$errors = array();
	
		$s_data = array();

// Set context from calling module
				
		$this->setSearch('assetsSearch', 'useDefault', $s_data);
		
		$collection = new AssetCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		parent::index($collection, $sh);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarAll($sidebarlist);
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		$flash->addError('delete is not allowed here');
		
		sendTo($this->name,'index',$this->_modules);
		
	}
	
	public function depreciation ()
	{
		
		$flash = Flash::Instance();
		
		$db = DB::Instance();
		
		$errors = array();
		
		$db->StartTrans();
		
		if (isset($this->_data['id']))
		{
			assetHandling::depreciateOne($this->_data['id'], $errors);
		} 
		else 
		{
			assetHandling::depreciateAll($errors);
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$db->FailTrans();
		}
		else
		{
			$db->CompleteTrans();
		}
		
		if (isset($this->_data['id']))
		{
			sendTo($this->name, 'view', $this->_modules, array('id'=>$this->_data['id']));
		}
		else
		{
			sendTo($this->name, 'index', $this->_modules);
		}
			
	}

	public function disposal ()
	{
		
		$flash = Flash::Instance();
		
		$db = DB::Instance();
		
		$errors = array();
		
		$db->StartTrans();
		
		if (isset($this->_data['id']))
		{
			
			assetHandling::depreciateOne($this->_data['id'], $errors);
			
			if (count($errors)>0)
			{
				$flash->addErrors($errors);
				$db->FailTrans();
				sendTo($this->name, 'view', $this->_modules, array('id'=>$this->_data['id']));
			}
			else
			{
				$db->CompleteTrans();
				$asset = $this->_uses['Asset'];
				$asset->load($this->_data['id']);
				$this->view->set('disposal_date', fix_date(date(DATE_FORMAT)));
				$this->view->set('disposal_value', 0);
				$this->view->set('asset', $asset);
			}
			
		}
		else
		{
			$flash->addError('Asset Disposal failed - no asset selected');
			sendTo($this->name,'index',$this->_modules);
		}
		
	}
	
	public function savedisposal ()
	{
		
		$flash = Flash::Instance();
		
		if (isset($this->_data['Asset']))
		{
			$data=$this->_data['Asset'];
		}
				
		if (isset($data['id']))
		{
			
			$id = $data['id'];
			
			if (strtolower($this->_data['saveform'])=='cancel')
			{
				$flash->addMessage('Action cancelled');
				sendTo($this->name, 'view', $this->_modules, array('id'=>$id));
			}
			
			$db = DB::Instance();
			
			$errors = array();
			
			$db->StartTrans();

			assetHandling::disposal($id, $data, $errors);
			
			if (count($errors)>0)
			{
				$flash->addErrors($errors);
				$db->FailTrans();
				$this->_data['id'] = $id;
				$this->disposal();
				$this->_templateName = $this->getTemplateName('disposal');
				$this->view->set('disposal_date', fix_date($data['disposal_date']));
				$this->view->set('disposal_value', $data['disposal_value']);
				return;
			}
			else
			{
				$db->CompleteTrans();
			}
			
			sendTo($this->name, 'view', $this->_modules, array('id'=>$id));
			
		}
		
		sendTo($this->name, 'view', $this->_modules);
		
	}
	
	public function save()
	{
		$flash = Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();
		
		$errors = array();
		
		assetHandling::save($this->_data['Asset'], $errors);
		
		if (count($errors)===0 && $db->CompleteTrans()) { 
			if (strtolower($this->_data['saveform'])=='save') {
				sendTo($this->name, 'index', $this->_modules);
			} else {
				sendTo($this->name, 'new', $this->_modules);
			}
		}
		
		$db->FailTrans();
		$flash->addErrors($errors);
		$this->_data['id'] = '';
		$this->refresh();

	}

	public function view()
	{

		if (isset($this->_data['id']))
		{
			$asset = $this->_uses['Asset'];
			$asset->load($this->_data['id']);

			$sidebar = new SidebarController($this->view);
			$sidebarlist=array();
			$sidebarlist['showall']= array(
							'tag'=>'View All Assets',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'index'
													 )
											   )
							);
							
			$this->sidebarAll($sidebarlist);
			$sidebar->addList('Actions',$sidebarlist);
			
			$sidebarlist=array();
			$sidebarlist['viewtrans']= array(
							'tag'=>'View Transactions',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'artransactions'
													 ,'action'=>'index'
													 ,'armaster_id'=>$this->_data['id']
													 )
											   )
							);
							
			if (is_null($asset->disposal_date))
			{
				$sidebarlist['disposal']= array(
								'tag'=>'Disposal',
								'link'=>array_merge($this->_modules
												   ,array('controller'=>$this->name
														 ,'action'=>'disposal'
														 ,'id'=>$this->_data['id']
														 )
												   )
								);
			}
			
			if (is_null($asset->disposal_date) && $asset->wd_value>0)
			{
				$sidebarlist['depreciation']= array(
								'tag'=>'Run Depreciation',
								'link'=>array_merge($this->_modules
												   ,array('controller'=>$this->name
														 ,'action'=>'depreciation'
														 ,'id'=>$this->_data['id']
														 )
												   )
								);
			}
			
			$sidebar->addList('This Asset',$sidebarlist);
			
			$this->view->register('sidebar',$sidebar);
			$this->view->set('sidebar',$sidebar);
		}
		else
		{
			sendTo($this->name,'index',$this->_modules);
		}
		
	}

	protected function sidebarAll (&$sidebarlist=array())
	{
		
		$sidebarlist['viewtrans']= array(
							'tag'=>'View All Transactions',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'artransactions'
													 ,'action'=>'index'
													 )
											   )
							);
							
		$sidebarlist['newasset']= array(
							'tag'=>'New Asset',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'new'
													 )
											   )
							);
							
		$sidebarlist['depreciation']= array(
							'tag'=>'Run Depreciation',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'depreciation'
													 )
											   )
							);
							
		
	}
	
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'Assets':$base), $action);
	}

}

// End of AssetsController
