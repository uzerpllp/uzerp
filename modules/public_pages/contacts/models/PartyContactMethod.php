<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyContactMethod extends DataObject
{
	
	protected $version='$Revision: 1.14 $';
	
	protected $defaultDisplayFields=array('name'=>'Name'
										 ,'contact'=>'Contact'
										 ,'main'=>'Main'
										 ,'billing'=>'Billing'
										 ,'shipping'=>'Shipping'
										 ,'payment'=>'Payment'
										 ,'technical'=>'Technical');
	
	function __construct($tablename='party_contact_methods')
	{
		
		parent::__construct($tablename);
		
		$this->idField='id';

 		$this->belongsTo('Contactmethod', 'contactmethod_id', 'contact');
 		$this->hasOne('Contactmethod', 'contactmethod_id', 'contactmethod');
 		$this->hasOne('Party', 'party_id', 'party');
 		
 		$this->setEnum('type',array('T'=>'phone'
 								   ,'F'=>'fax'
 								   ,'M'=>'mobile'
 								   ,'E'=>'email'));
 								   
 		$this->getField('name')->setDefault('MAIN');
	}

	function delete($id = null, &$errors = [], $archive = false, $archive_table = null, $archive_schema = null)
	{
		
		if (empty($id))
		{
			$errors[] = 'No Party Contact Method identifier';
			return false;
		}
		
		$this->load($id);
		
		if (!$this->isLoaded())
		{
			$errors[] = 'Cannot find Party Contact Method record';
			return false;
		}
		
		$db = DB::Instance();
		$flash = Flash::Instance();
		
		$db->StartTrans();
		
		if (!parent::delete())
		{
			$db->FailTrans();
			$flash->addError($db->ErrorMsg());
		}
		
		// Delete the Contact Method if it is not used elsewhere
		if (!$this->contactmethod->delete($this->contactmethod->id, $errors))
		{
			$db->FailTrans();
			$flash->addError($db->ErrorMsg());
		}

		return $db->CompleteTrans();
		
	}
		
	function save($debug = false)
	{
		
		if ($this->contactmethod_id!='')
		{
			
			$partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
			
			$partycontactmethod->load($this->id);
			
			if ($partycontactmethod->isLoaded() && $this->contactmethod_id != $partycontactmethod->contactmethod_id)
			{
				// Update to Party Contact Method, now points to different Contact Method
				// so get the old Contact Method id for use below
				$old_contactmethod = $partycontactmethod->contactmethod_id;
			}
			
			$db = DB::Instance();
			$db->StartTrans();
			
			$errors = array();
			
			if ($this->main[0] == 't')
			{

				$partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
				
				$cc = new ConstraintChain();
				
				$cc->add(new Constraint('party_id', '=' ,$this->party_id));		
				$cc->add(new Constraint('"type"', '=' ,$this->type));		
				$cc->add(new Constraint('main', 'is' ,'true'));
				
				foreach ($partycontactmethod->getAll($cc) as $id=>$value)
				{
					if (!$partycontactmethod->update($id, 'main', 'false'))
					{
						break;
					}
				}
				
			}
			
			$result = parent::save();
			
			if ($result && !empty($old_contactmethod))
			{
				// Party Contact Method has been saved OK, and Contact Method has changed
				// so delete old Contact Method if no other party is linked to it
				$contactmethod = DataObjectFactory::Factory('ContactMethod');
				$result = $contactmethod->delete($old_contactmethod, $errors);
			}
			
			if ($result === FALSE || count($errors)>0)
			{
				$flash = Flash::Instance();
				$flash->addErrors($errors);
				$db->FailTrans();
			}
			
			$db->CompleteTrans();
			
		}
		
		return true;
		
	}

	public function check(&$data)
	{
		
		$contactmethod = DataObjectFactory::Factory('Contactmethod');
			
// If the contactmethod id field is present, this is an update
// - if there is no change to contactmethod, do nothing
// - if no one else has this contactmethod, do nothing
// - otherwise, treat as an insert
		if (empty($data['Contactmethod'][$contactmethod->idField]))
		{
			// Use existing contact method if match found
			$contactmethod->check_exists($data['Contactmethod']);
			
			if ($contactmethod->isLoaded())
			{
				foreach ($data['Contactmethod'] as $fieldname=>$value)
				{
					if ($contactmethod->isField($fieldname))
					{
						$data['Contactmethod'][$fieldname] = $contactmethod->$fieldname;
					}
				}
				$data['PartyContactMethod']['contactmethod_id'] = $contactmethod->{$contactmethod->idField};
				
			}
			
			return;
		}
		
		$contactmethod->load($data['Contactmethod'][$contactmethod->idField]);
		
		// get count of party contact methods for this contact method
		$count = $contactmethod->parties->count();
		
// If the contact method exists then there should be a party for it!
		if ($count==0)
		{
			return;
		}

// if there is only one party contact method for the contact method
// and we are not inserting a party contact method then
// if input contact is not empty, check if used elsewhere
		if ($count==1 && !empty($data['PartyContactMethod'][$this->idField]))
		{
			if (!empty($data['Contactmethod']['contact']))
			{
				$check_cm = DataObjectFactory::Factory('Contactmethod');
				$check_cm->check_exists($data['Contactmethod']);
				if ($check_cm->isLoaded())
				{
					$data['PartyContactMethod']['contactmethod_id'] = $data['Contactmethod'][$contactmethod->idField] = $check_cm->{$contactmethod->idField};
				}
			}
			return;
		}
		
		if ($contactmethod->isLoaded() && $data['Contactmethod']['contact'] == $contactmethod->contact)
		{
			// No change to contact method
			return;
		}
		
// This is a change to the existing contactmethod
// which is linked to more than one party
// so insert it
		$data['Contactmethod'][$contactmethod->idField]='';
		$data['PartyContactMethod']['contactmethod_id']='';
		
	}
	
	public function getType ($name)
	{
		
		foreach ($this->getEnumOptions('type') as $key=>$value)
		{
			if ($name===$value) {
				return $key;
			}
		}
		
		return '';
		
	}
	
}

// End of PartyContactMethod
