<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyaddresssController extends Controller
{

	protected $version='$Revision: 1.8 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('PartyAddress');
		$this->uses($this->_templateobject);
	
		$this->related['company'] = array('clickaction'=>'edit'
										 ,'allow_delete'=>TRUE);
		$this->related['person'] = array('clickaction'=>'edit'
										,'allow_delete'=>TRUE);
		
	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new PartyAddressCollection($this->_templateobject));
		
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
		
		$partyaddress = $this->_uses[$this->modeltype];
		
		if(!empty($this->_data[$this->modeltype][$partyaddress->idField]))
		{
			// This is an update to an existing party address/address
			// so check if the address is used by other parties
			// - update it if address is only used by this party
			// - insert it if the current address is used by other parties and it does not already exist
			// - otherwise, no need to do anything here (but see below)
			if ($partyaddress->load($this->_data[$this->modeltype][$partyaddress->idField]))
			{
				$partyaddress->checkAddress($this->_data);
			}
			else
			{
				$errors[] = 'Error loading current Address details '.$db->errorMsg();
			}
			
		}
		
		if (isset($this->_data['Address']))
		{
			// Check if this address exists; if it does, point the PartyAddress to it
			// and remove the input address as it does not need inserting/updating
			$address	= DataObjectFactory::Factory('Address');
			$address->check_exists($this->_data['Address']);
			
			if ($address->isLoaded())
			{
				unset($this->_data['Address']);
				
				$this->_data[$this->modeltype]['address_id'] = $address->{$address->idField};
			}
		}
		
		if(count($errors)==0 && parent::save($this->modeltype))
		{
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	protected function getPageName($base=null,$type=null)
	{
		return parent::getPageName((empty($base)?'Addresss':$base),$type);
	}

}

// End of PartyaddresssController
