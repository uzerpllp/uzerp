<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartycontactmethodsController extends Controller
{

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('PartyContactMethod');
		
		$this->uses($this->_templateobject);
		
		$this->related['company'] = array('clickaction'=>'edit'
										 ,'allow_delete'=>TRUE);
		$this->related['person'] = array('clickaction'=>'edit'
										,'allow_delete'=>TRUE);

	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new PartyContactMethodCollection($this->_templateobject));
	
	}

	public function _new()
    {
        parent::_new();
		// This sets some smarty vars so that the new/edit form
		// only shows the email trailer/message edit area
		// when editing details for the system company.
		$this->view->set('editing_sysco', false);
		$pid = $this->_uses['PartyContactMethod']->party_id ?? $this->_data['party_id'];
		$company = new Company();
		$company->loadBy('party_id', $pid);
		if ($company->id == COMPANY_ID) {
			$this->view->set('editing_sysco', true);
		}
    }

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendBack();
		
	}

	public function save()
	{
		
		$flash=Flash::Instance();
		$errors = array();
		
		$partycontactmethod = $this->_uses[$this->modeltype];
		$pcm_idfield		= $partycontactmethod->idField;
		
		$pcm_data			= $this->_data['PartyContactMethod'];
		$cm_data			= $this->_data['Contactmethod'];
		
		if (empty($pcm_data[$pcm_idfield]))
		{
			if (empty($cm_data['contact']))
			{
				$errors[] = 'No Contact details entered';
			}
		}
		else
		{
			if ($partycontactmethod->load($pcm_data[$pcm_idfield]))
			{
				$partycontactmethod->check($this->_data);
			}
			else
			{
				$errors[] = 'Error loading current Contact details '.$db->errorMsg();
			}
		}		
				
		$db = DB::Instance();
		$db->StartTrans();
		
		if (!empty($cm_data['contact']))
		{
			// Check if this contact exists; if it does, point the PartyContactMethod to it
			// and remove the input contact method as it does not need inserting/updating
			$contactmethod = DataObjectFactory::Factory('ContactMethod');

			$contactmethod->check_exists($cm_data);
			
			if ($contactmethod->isLoaded())
			{
				unset($this->_data['Contactmethod']);
				
				$this->_data[PartyContactMethod]['contactmethod_id'] = $contactmethod->{$contactmethod->idField};
			}
		}
		else
		{
			// Blanking out (deleting) current details
			$partycontactmethod->delete($partycontactmethod->id, $errors);
			unset($this->_data['Contactmethod']);
			unset($this->_data['PartyContactMethod']);
		}
		
		if(count($errors)==0 && parent::save($this->modeltype))
		{
			$db->CompleteTrans();
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		$flash->addErrors($errors);
		$this->refresh();

	}

	protected function getPageName($base = null, $type = null)
	{

		if (isset($this->_data['type']) && empty($base))
		{
			$pcm = DataObjectFactory::Factory('PartyContactMethod');
			
			$types=$pcm->getEnumOptions('type');
			
			$base=$types[$this->_data['type']];
		}
		
		if (isset($this->_data['party_id']))
		{
			$party = DataObjectFactory::Factory('Party');
			
			$party->load($this->_data['party_id']);
			
			$base.=' for '.$party->getPartyIdentifierValue();
		}
		
		return parent::getPageName((empty($base)?'Contact Methods':$base), $type);
	
	}

}

// End of PartycontactmethodsController
