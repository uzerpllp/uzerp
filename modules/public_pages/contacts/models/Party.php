<?php

/** 
 *	(c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Party extends DataObject
{
	
	protected $version = '$Revision: 1.14 $';
	
	function __construct($tablename='party')
	{
		
		parent::__construct($tablename);
		
		$this->idField = 'id';

		$this->hasMany('PartyContactMethod','contactmethods', 'party_id', null, TRUE);
		$this->hasMany('PartyAddress','addresses', 'party_id', null, TRUE);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$this->setAlias('main_address', 'PartyAddress', $cc, 'main_address', array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country'), 'party_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','T'));
		$this->setAlias('phone','PartyContactMethod',$cc,'contact', array(),'party_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','M'));
		$this->setAlias('mobile','PartyContactMethod',$cc,'contact', array(),'party_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','F'));
		$this->setAlias('fax','PartyContactMethod',$cc,'contact', array(),'party_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','E'));
		$this->setAlias('email','PartyContactMethod',$cc,'contact', array(),'party_id');
		
	}

	public static function mappings ()
	{
		return array('model'=>self::get_name()
					,'tablename'=>'party');
	}

	public function getAddress ($name='', $type='')
	{

		$address = DataObjectFactory::Factory('PartyAddress');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('party_id', '=', $this->party_id));
		
		if (empty($name) && empty($type))
		{
			$cc->add(new Constraint('main', '=', true));
		}
		
		if (!empty($name))
		{
			$cc->add(new Constraint('name', '=', $name));
		}
		
		if (!empty($type))
		{
			$cc->add(new Constraint($type, '=', true));
		}
		
		$address->loadBy($cc);
// might want an option here to support other formats
// 1) Print address lines including blank lines
// 2) Print address lines excluding blank lines, spacing x lines after
// 3) This format - print address lines excluding blank lines
		if ($address->isLoaded()) 
		{
			return $address->address;
		}
		else
		{
			return DataObjectFactory::Factory('Address');
		}
	}

	public function getContactDetail($type, $name='')
	{

		$contact = DataObjectFactory::Factory('PartyContactMethod');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('party_id', '=', $this->party_id));
		
		if (!empty($name))
		{
			$cc->add(new Constraint('name', '=', $name));
		}
		else
		{
			$cc->add(new Constraint('main', 'is', 'true'));
		}
		
		$cc->add(new Constraint('"type"', '=', $type));
		
		if ($contact->loadBy($cc))
		{
			return $contact->contact;
		}
		else
		{
			return '';
		}
		
	}

	public function getNote($title='', $note_type='')
	{
		$note = DataObjectFactory::Factory('PartyNote');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('party_id','=',$this->party_id));
		
		if (!empty($title))
		{
			$cc->add(new Constraint('lower(title)','=',strtolower($title)));
		}
		
		if (!empty($note_type))
		{
			$cc->add(new Constraint('lower(note_type)','=',strtolower($note_type)));
		}
		else
		{
			$cc->add(new Constraint('note_type','=','contacts'));
		}
		
		$note->loadBy($cc);
		
		return $note->note;
		
	}
	
	public function getPhoneNumbers()
	{
		return $this->getContactMethods('T');
	}
	
	public function getContactMethods($type='', $cc='')
	{
		$cms = new PartyContactMethodCollection();

		if (get_class($this) == 'Person' && $this->party_id == '') {
			return $cms;
		}
		
		$sh = new SearchHandler($cms,false);
		$sh->setOrderby(array('type', 'name'));
		
		if (!empty($type))
		{
			$sh->addConstraint(new Constraint('type','=',$type));
		}
		
		$sh->addConstraint(new Constraint('party_id','=',$this->party_id));
		
		if (!empty($cc) && $cc instanceOf ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$cms->load($sh);
		
		return $cms;
	}
	
	public function getFaxNumbers()
	{
		return $this->getContactMethods('F');
	}
	
	public function getEmailAddresses()
	{
		return $this->getContactMethods('E');
	}
	
	public function getPartyIdentifierValue()
	{
		
		switch ($this->type)
		{
			case 'Company':
				$party = DataObjectFactory::Factory('Company');
				break;
			case 'Person':
				$party = DataObjectFactory::Factory('Person');
				break;
			default:
				return '';
		}
		
		$party->loadBy('party_id', $this->id);
		
		return $party->getIdentifierValue();
	}

	public function delete($id = null, &$errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
	{
		$db = DB::Instance();
		$db->startTrans();

		// Only for Party, not sub classes
		if (isset($id)) {
			$this->load($id);
			$adr = $this->addresses;
			$sh = new SearchHandler(new PartyAddressCollection(), false);
			$sh->addConstraint(new Constraint('party_id', '=', $this->party_id));
			$adr->load($sh);

			$cmt = $this->contactmethods;
			$sh = new SearchHandler(new ContactmethodCollection(), false);
			$sh->addConstraint(new Constraint('party_id', '=', $this->party_id));
			$cmt->load($sh);
		}

		$address_ids = array();
		foreach ($adr as $partyaddress)
		{
			$address_ids[$partyaddress->address_id] = $partyaddress->address_id;
		}
		
		$contact_methods_ids = array();
		foreach ($cmt as $contactmethod)
		{
			$contact_methods_ids[$contactmethod->contactmethod_id] = $contactmethod->contactmethod_id;
		}

		// If this is subtype of party, delete it then delete party
		if ($this->isLoaded() && $this->subClass)
		{
			$result = parent::delete(null, $errors);
			if ($result)
			{
				$result	= $this->party->delete(null, $errors);
			}
		}
		else
		{
			$result	= parent::delete($id, $errors);
		}
		
		if ($result && count($address_ids) > 0)
		{
			// Check for and delete any orphan addresses
			$partyaddress	= DataObjectFactory::Factory('PartyAddress');
			$address		= DataObjectFactory::Factory('Address');
			
			foreach ($address_ids as $address_id)
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('address_id', '=', $address_id));
				$count = $partyaddress->getCount($cc);
			
				if ($count == 0)
				{
					$result = $address->delete($address_id, $errors);
					if (!$result)
					{
						break;
					}
				}			
			}
		}
		
		if ($result && count($contact_methods_ids) > 0)
		{
			// Check for and delete any orphan contact methods
			$partycontactmethod	= DataObjectFactory::Factory('PartyContactMethod');
			$contactmethod		= DataObjectFactory::Factory('ContactMethod');
			
			foreach ($contact_methods_ids as $contact_methods_id)
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('contactmethod_id', '=', $contact_methods_id));
				
				if ($partycontactmethod->getCount($cc) == 0)
				{
					$result = $contactmethod->delete($contact_methods_id, $errors);
					if (!$result)
					{
						break;
					}
				}			
			}
		}
		
		if (!$result)
		{
			$db->FailTrans();
		}
		
		$db->completeTrans();

		return $result;
		
	}
	
}

// End of Party
