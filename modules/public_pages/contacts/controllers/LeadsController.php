<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LeadsController extends printController
{

	protected $version = '$Revision: 1.21 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Lead');
		$this->uses($this->_templateobject);
		
		$this->related['addresses'] = array('clickaction'=>'edit');

	}

	public function index()
	{
		$this->view->set('clickaction', 'view');

		$s_data = array();

		$this->setSearch('CompanySearch', 'leads', $s_data);
		
		parent::index($t = new LeadCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'contacts','controller'=>'companys','action'=>'new'),
					'tag'=>'new_account'
				),
				'new_lead'=>array(
					'link'=>array('module'=>'contacts','controller'=>'leads','action'=>'new'),
					'tag'=>'new_lead'
				),
				'new_person'=>array(
					'link'=>array('module'=>'contacts','controller'=>'persons','action'=>'new'),
					'tag'=>'new_person'
				)									
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new()
	{
		$ao = &AccessObject::Instance(EGS_USERNAME);
		
		if($ao->hasPermission('crm'))
		{
			$this->view->set('crm_access',true);
		}
		
		$categories = DataObjectFactory::Factory('Contactcategory');
		$this->view->set('contact_categories',$categories->getCompanyCategories());
		
		parent::_new();
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		$company=$this->_uses['Lead'];
		
		$company->load($this->_data['id']);

		if (!$company->isLoaded())
		{
			$flash = Flash::instance();
			$flash->addError('You do not have permission to delete this lead.');
			sendTo($this->name, 'index', $this->_modules);
			return;
		}
		
		$pl = new PreferencePageList('recently_viewed_leads'.EGS_COMPANY_ID);
		$pl->removePage(new Page(array('module'=>'contacts','controller'=>'leads','action'=>'view','id'=>$company->id),'company',$company->name));
		$pl->save();
		
		parent::delete('Company');
		
		sendTo('Leads','index',array('contacts'));
	}

	public function edit()
	{
		$company=$this->_uses['Lead'];
		
		if (!isset($this->_data[$company->idField]))
		{
			sendTo();
		}
		
		$company->load($this->_data[$company->idField]);
		
		if (!$company->isLoaded())
		{
			$flash = Flash::instance();
			$flash->addError('You do not have permission to edit this lead.');
			sendTo($this->name, 'index', $this->_modules);
			return;
		}
		
				
		$addresslist=array();
		
		if ($company)
		{
			foreach ($company->addresses as $address)
			{
				$addresslist[$address->id]=$address->address;
			}
		}
		
		$this->view->set('addresses',$addresslist);
		
		$cic = DataObjectFactory::Factory('CompanyInCategories');
		$selected=$cic->getCategoryID($company->id);
		
		$this->view->set('selected_categories',$selected);
		
		parent::edit();
	}

	public function view()
	{
		$company=$this->_uses['Lead'];
		$company->load($this->_data[$company->idField],true);
		
		if (!$company->isLoaded())
		{
			$flash = Flash::instance();
			$flash->addError('You do not have permission to view this lead.');
			sendTo($this->name, 'index', $this->_modules);
			return;
		}
		
		$sidebar=new SidebarController($this->view);
		
		$sidebar->addList(
			'currently_viewing',
			array(
				$company->name => array(
					'tag' => $company->name,
					'link' => array('module'=>'contacts','controller'=>'leads','action'=>'view','id'=>$company->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('module'=>'contacts','controller'=>'leads','action'=>'edit','id'=>$company->id)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array('module'=>'contacts','controller'=>'leads','action'=>'delete','id'=>$company->id),
					'class' => 'confirm',
					'data_attr' => ['data_uz-confirm-message' => "Delete {$company->name}?|This cannot be undone."]
				),
				'sharing' => array(
					'tag' => 'Sharing',
					'link' => array('module'=>'contacts','controller'=>'companys','action'=>'sharing','id'=>$company->id)
				),
				'convert_to_account' => array(
					'tag' => 'convert_to_account',
					'link' => array('module'=>'contacts','controller'=>'leads','action'=>'converttoaccount','id'=>$company->id)
				)
			)
		);
		
		$sidebar->addList(
			'related_items',
			array(
				'people'=>array(
					'tag'=>'People',
					'link'=>array('module'=>'contacts','controller'=>'persons','action'=>'viewcompany','company_id'=>$company->id),
					'new'=>array('module'=>'contacts','controller'=>'persons','action'=>'new','company_id'=>$company->id)
				),
				'spacer',
				'opportunities'=>array(
					'tag'=>'Opportunities',
					'link'=>array('module'=>'crm','controller'=>'opportunitys','action'=>'viewcompany','company_id'=>$company->id),
					'new'=>array('module'=>'crm','controller'=>'opportunitys','action'=>'new','company_id'=>$company->id)
				),
				'activities'=>array(
					'tag'=>'Activities',
					'link'=>array('module'=>'crm','controller'=>'activitys','action'=>'viewcompany','company_id'=>$company->id),
					'new'=>array('module'=>'crm','controller'=>'activitys','action'=>'new','company_id'=>$company->id)
				),
				'notes'=>array(
					'tag'=>'Notes',
					'link'=>array('module'=>'contacts','controller'=>'partynotes','action'=>'viewcompany','party_id'=>$company->party_id),
					'new'=>array('module'=>'contacts','controller'=>'partynotes','action'=>'new','party_id'=>$company->party_id)
				),
				'spacer',
				'attachments'=>array(
					'tag'=>'Attachments',
					'link'=>array('module'=>'contacts','controller'=>'partyattachments','action'=>'viewcompany','party_id'=>$company->party_id),
					'new'=>array('module'=>'contacts','controller'=>'partyattachments','action'=>'new','entity_table'=>'party','entity_id'=>$company->party_id)
				),
				'spacer',
				'addresses'=>array(
					'tag'=>'Addresses',
					'link'=>array('module'=>'contacts','controller'=>'partyaddresss','action'=>'viewcompany','party_id'=>$company->party_id),
					'new'=>array('module'=>'contacts','controller'=>'partyaddresss','action'=>'new','party_id'=>$company->party_id)
				),
				'spacer',
				'phone'=>array(
					'tag'=>'Phone',
					'link'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'viewcompany','party_id'=>$company->party_id,'type'=>'T'),
					'new'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'new','party_id'=>$company->party_id,'type'=>'T')
				),
				'fax'=>array(
					'tag'=>'Fax',
					'link'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'viewcompany','party_id'=>$company->party_id,'type'=>'F'),
					'new'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'new','party_id'=>$company->party_id,'type'=>'F')
				),
				'email'=>array(
					'tag'=>'Email',
					'link'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'viewcompany','party_id'=>$company->party_id,'type'=>'E'),
					'new'=>array('module'=>'contacts','controller'=>'partycontactmethods','action'=>'new','party_id'=>$company->party_id,'type'=>'E')
				),
				'spacer',
				'meetings'=>array(
					'tag'=>'Meetings',
					'link'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'viewcompany','company_id'=>$company->id),
					'new'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'new','company_id'=>$company->id)
				)
			)
		);
		
		$ao = & AccessObject::Instance(EGS_USERNAME);
		
		if($ao->hasPermission('crm'))
		{
			$this->view->set('crm_access',true);
		}
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$category	= DataObjectFactory::Factory('CompanyInCategories');
		$this->view->set('categories',implode(',', $category->getCategorynames($company->id)));
		
		if($company instanceof Company)
		{
			$pl = new PreferencePageList('recently_viewed_leads'.EGS_COMPANY_ID);
			$pl->addPage(new Page(array('module'=>'contacts','controller'=>'leads','action'=>'view','id'=>$company->id),'company',$company->name));
			$pl->save();
		}
	}
	
	public function converttoaccount()
	{
		$company = $this->_uses['Lead'];
		
		if (isset($this->_data['Lead']) && isset($this->_data['Lead'][$company->idField]))
		{
			$id=$this->_data['Lead'][$company->idField];
			$data=$this->_data['Lead'];
		}
		elseif (isset($this->_data[$company->idField]))
		{
			$id=$this->_data[$company->idField];
		}
		else
		{
			$flash=Flash::Instance();
			$flash->addError('Select a Lead to convert');
			sendTo('leads','index', array('contacts'));
		}
		
		$company->load($id);
		
		if (!$company->isLoaded())
		{
			$flash = Flash::instance();
			$flash->addError('You do not have permission to edit this lead.');
			sendTo($this->name, 'index', $this->_modules);
			return;
		}
		
		$pl = new PreferencePageList('recently_viewed_leads'.EGS_COMPANY_ID);
		$pl->removePage(new Page(array('module'=>'contacts','controller'=>'leads','action'=>'view','id'=>$company->id),'company',$company->name));
		$pl->save();
		
		$pl = new PreferencePageList('recently_viewed_companies'.EGS_COMPANY_ID);
		$pl->addPage(new Page(array('module'=>'contacts','controller'=>'companys','action'=>'view','id'=>$company->id),'company',$company->name));
		$pl->save();
		
		$system_prefs = SystemPreferences::instance();
		$autoGenerate = $system_prefs->getPreferenceValue('auto-account-numbering', 'contacts');
		
		if(!(empty($autoGenerate) || $autoGenerate === 'off'))
		{
			$company->update($id,array('is_lead', 'accountnumber'), array('false', $company->createAccountNumber()));
			sendTo('companys', 'view', array('contacts'), array('id'=>$company->id));
		}
		else
		{
			if (isset($data['accountnumber']))
			{
				$company->update($id,array('is_lead','accountnumber'),array('false', $data['accountnumber']));
				sendTo('companys','view', array('contacts'),array('id'=>$company->id));
			}
			else
			{
				parent::_new();
			}
			
		}
	}
	
	public function save()
	{
		$flash=Flash::Instance();
		
		$errors = array();
		
		$company=$this->_uses['Lead'];
		
		if(isset($this->_data['Lead'][$company->idField]) && !empty($this->_data['Lead'][$company->idField]))
		{
			
			$company->load($this->_data['Lead'][$company->idField]);
			
			if($company===false)
			{
				echo 'Could not load Company for id='.$this->_data['Lead'][$person->idField].' - Abandoned<br>';
				sendBack();
			}
		}
		
		$db=&DB::Instance();
		$db->StartTrans();

		$partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
		
		foreach ($partycontactmethod->getEnumOptions('type') as $key=>$type)
		{
			if (isset($this->_data[$type]['PartyContactMethod'])
				 && isset($this->_data[$type]['Contactmethod'])
				 && empty($this->_data[$type]['Contactmethod']['contact']))
			{
					if (!empty($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField]))
					{
						$partycontactmethod->delete($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField], $errors);
					}
					unset($this->_data[$type]);
			}
		}

		if(count($errors)==0 && parent::save('Lead'))
		{
			foreach ($this->saved_models as $model)
			{
				if (isset($model['Lead']))
				{
					$company=$model['Lead'];
					break;
				}
			}
			
			$this->company_id=$company->id;
			
			if(isset($this->_data['Lead']['crm']))
			{
				$crm_data=$this->_data['Lead']['crm'];
				
				$ao = &AccessObject::Instance(EGS_USERNAME);
				
				if($ao->hasPermission('crm'))
				{
					$crm_data['company_id']=$company->{$company->idField};
					parent::save('CompanyCrm',$crm_data);
				}
				
			}
			
			$category = DataObjectFactory::Factory('CompanyInCategories');
			$current_categories	= $category->getCategoryID($company->{$company->idField});

			$check_categories = array();
			
			if (isset($this->_data['ContactCategories']))
			{
				$delete_categories = array_diff($current_categories, $this->_data['ContactCategories']['category_id']);
				$insert_categories = array_diff($this->_data['ContactCategories']['category_id'], $current_categories);
			}
			
			$result = TRUE;
			
			if (!empty($delete_categories))
			{
				$result = $category->delete(array_keys($delete_categories), $errors);
			}
			
			if (!empty($insert_categories) && $result)
			{
				$result = $category->insert($insert_categories, $company->{$company->idField});
			}
			
			if ($result)
			{
				$db->CompleteTrans();
				sendTo($this->name, 'view', $this->_modules, array($company->idField=>$company->{$company->idField}));
			}
		}
			
		// Errors
		$flash->addErrors($errors);
		
		$db->FailTrans();
		
		$db->CompleteTrans();
		
		$this->refresh();
		
	}
	
	protected function getPageName($base=null,$type=null)
	{
		return parent::getPageName((empty($base)?'lead':$base),$type);
	}


}

// End of LeadsController
