<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlaccountsController extends LedgerController
{

	protected $version='$Revision: 1.13 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new GLAccount();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->setSearch('glaccountsSearch', 'useDefault');
		
		$this->view->set('clickaction', 'view');
		parent::index(new GLAccountCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'new'
											 )
									   ),
					'tag'=>'new_glaccount'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();
		
		$account = $this->_uses[$this->modeltype];
		
		$centres = new GLCentre();
		$centres_list = $centres->getAll();
		$this->view->set('centres', $centres_list);
		
		$this->view->set('actypes',$account->getEnumOptions('actype'));
		
		if ($account->isLoaded())
		{
			$this->view->set('selected_centres', $account->getCentreIds());
			$this->view->set('selected_actype', $account->getEnumValue('actype'));
		}
		else
		{
			$this->view->set('selected_centres', array());
			$this->view->set('selected_actype', '');
		}
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		$flash=Flash::Instance();
		$errors=array();
 		$db = DB::Instance();
		$db->StartTrans();
		
		$current_centres=array();
 		
		if (isset($this->_data[$this->modeltype]['id']))
		{
			$account=$this->_templateobject;
			$account->load($this->_data[$this->modeltype]['id']);
			if ($account)
			{
				foreach ($account->centres as $accountcentre)
				{
					// Delete any existing entries not in the supplied list
					if (!empty($this->_data[$this->modeltype]['centre_id']) && in_array($accountcentre->glcentre_id, $this->_data[$this->modeltype]['centre_id']))
					{
						$current_centres[$accountcentre->glcentre_id]=$accountcentre->glcentre_id;
					}
					elseif(!$accountcentre->delete(null, $errors))
					{
						$errors[]='Failed to update Account Centre Reference '.$accountcentre->glcentre;
					}
				}
			}
		}
		
		if(count($errors)===0 && parent::save_model($this->modeltype))
		{
			if (!empty($this->_data[$this->modeltype]['centre_id']))
			{
				foreach ($this->_data[$this->modeltype]['centre_id'] as $centre_id)
				{
					if (!key_exists($centre_id, $current_centres))
					{
						$data=array();
						$data['glaccount_id']=$this->saved_model->id;;
						$data['glcentre_id']=$centre_id;
						$accountcentre=GLAccountCentre::Factory($data, $errors, 'GLAccountCentre');
						if ($accountcentre)
						{
							$accountcentre->save();
							if (!$accountcentre)
							{
								$errors[]='Failed to save reference to Centre';
								break;
							}
						}
						else
						{
							$errors[]='Failed to save reference to Centre';
							break;
						}
					}
				}
			}
			if (count($errors)==0)
			{
				$db->CompleteTrans();
				sendTo($this->name,'index', $this->_modules);
			}
		}
		else
		{
			$errors[]='Failed to save GL Account';
		}
		
		$db->FailTrans();
		$flash->addErrors($errors);
		$this->refresh();

	}

}

// End of GlaccountsController
