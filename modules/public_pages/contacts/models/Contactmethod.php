<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class Contactmethod extends DataObject
{
    protected $version = '$Revision: 1.10 $';

    protected $defaultDisplayFields = [
        'name' => 'Name',
        'contact' => 'Contact',
    ];

    public function __construct($tablename = 'contact_methods')
    {
        parent::__construct($tablename);

        $this->idField = 'id';

        $this->identifierField = 'contact';

        $this->hasMany('PartyContactMethod', 'parties', 'contactmethod_id');
    }

    public function __toString()
    {
        $value = $this->_fields['contact']->value;

        if (empty($value)) {
            $value = '';
        }

        return $value;
    }

    public function check_exists($data = [])
    {
        $cc = new ConstraintChain();

        if (! empty($data['contact'])) {
            $db = DB::Instance();
            $cc->add(new Constraint('contact', '=', $db->qstr($data['contact'])));
        } else {
            $cc->add(new Constraint('contact', 'is', 'NULL'));
        }

        $this->loadBy($cc);
    }

    public function delete($id = null, &$errors = [], $archive = false, $archive_table = null, $archive_schema = null)
    {
        if (! $this->isLoaded()) {
            if (empty($id)) {
                $errors[] = 'No Contact Method identifier';
                return false;
            } else {
                $this->load($id);
                if (! $this->isLoaded()) {
                    $errors[] = 'Cannot find Contact Method record';
                    return false;
                }
            }
        }

        $partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
        $cc = new ConstraintChain();
        $cc->add(new Constraint('contactmethod_id', '=', $this->id));
        if ($partycontactmethod->getCount($cc) == 0 && ! parent::delete($this->id, $errors)) {
            return false;
        }

        return true;
    }

    public static function Factory($data, &$errors = [], $do_name = null)
    {
        $cm = DataObjectFactory::Factory($do_name);
        $cm->check_exists($data);

        if ($cm->isLoaded()) {
            return $cm;
        }

        return parent::factory($data, $errors, $cm);
    }
}

// End of Contactmethod
