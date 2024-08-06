<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlcentresController extends LedgerController
{

	protected $version='$Revision: 1.11 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new GLCentre();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->setSearch('glcentresSearch', 'useDefault');

		$this->view->set('clickaction', 'view');
		parent::index(new GLCentreCollection($this->_templateobject));

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
					'tag'=>'new_glcentre'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();

		$accounts = new GLAccount();
		$this->view->set('accounts', $accounts->getAll());

		$centre=$this->_uses[$this->modeltype];
		if ($centre->isLoaded())
		{
			$this->view->set('selected_accounts', $centre->getAccountIds());
		}
		else
		{
			$this->view->set('selected_accounts', array());
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
		
		$current_accounts=array();
 		
		if (isset($this->_data[$this->modeltype]['id']))
		{
			$centre=$this->_templateobject;
			$centre->load($this->_data[$this->modeltype]['id']);
			if ($centre->isLoaded())
			{
				foreach ($centre->accounts as $accountcentre)
				{
					// Delete any existing entries not in the supplied list
					if (!empty($this->_data[$this->modeltype]['account_id']) && in_array($accountcentre->glaccount_id, $this->_data[$this->modeltype]['account_id']))
					{
						$current_accounts[$accountcentre->glaccount_id]=$accountcentre->glaccount_id;
					}
					elseif(!$accountcentre->delete(null, $errors))
					{
						$errors[]='Failed to update Account Centre Reference '.$accountcentre->glaccount;
					}
				}
			}
		}
		
		if(count($errors)===0 && parent::save_model($this->modeltype))
		{
			if (isset($this->_data[$this->modeltype]['account_id']))
			{
				foreach ($this->_data[$this->modeltype]['account_id'] as $account_id)
				{
					if (!key_exists($account_id, $current_accounts))
					{
						$data=array();
						$data['glaccount_id']=$account_id;
						$data['glcentre_id']=$this->saved_model->id;
						$accountcentre=GLAccountCentre::Factory($data, $errors, 'GLAccountCentre');
						if ($accountcentre)
						{
							$accountcentre->save();
							if (!$accountcentre)
							{
								$errors[]='Failed to save reference to Account';
								break;
							}
						}
						else
						{
							$errors[]='Failed to save reference to Account';
							break;
						}
					}
				}
			}
			if (count($errors)==0)
			{
				$db->CompleteTrans();
				sendTo($this->name, 'index', $this->_modules);
			}
		}
		else
		{
			$errors[]='Failed to save GL Centre';
		}
		
		$db->FailTrans();
		$flash->addErrors($errors);
		$this->refresh();

	}

}

// End of GlcentresController
