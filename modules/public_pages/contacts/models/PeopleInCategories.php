<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PeopleInCategories extends DataObject
{
    protected $version = '$Revision: 1.6 $';

    public function __construct($tablename = 'people_in_categories')
    {
        parent::__construct($tablename);

        $this->idField = 'id';
        $this->identifierField = 'category_id';

        $this->hasMany('Person', 'id', 'company');
        $this->hasMany('ContactCategories', 'id', 'contactcategories');
    }

    public function delete($ids = null, &$errors = [], $archive = false, $archive_table = null, $archive_schema = null)
    {
        if (! empty($ids)) {
            if (! is_array($ids)) {
                $ids = [$ids];
            }

            $db = DB::Instance();
            $db->startTrans();

            foreach ($ids as $id) {
                if (! parent::delete($id, $errors)) {
                    $db->failTrans();
                    $db->completeTrans();
                    return false;
                }
            }

            $db->completeTrans();
        }

        return true;
    }

    public function getCategoryID($person_id)
    {
        $this->identifierField = 'category_id';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('person_id', '=', $person_id));

        return $this->getAll($cc);
    }

    public function getCategoryNames($person_id)
    {
        $this->identifierField = 'category';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('person_id', '=', $person_id));

        return $this->getAll($cc, null, true);
    }

    public function getPersonID($category_id, $cc = null)
    {
        if (! $cc instanceof ConstraintChain) {
            $cc = new ConstraintChain();
        }

        if (is_array($category_id)) {
            $cc->add(new Constraint('category_id', 'in', '(' . implode(',', $category_id) . ')'));
        } else {
            $cc->add(new Constraint('category_id', '=', $category_id));
        }

        $this->idField = 'person_id';
        $this->identifierField = ['surname', 'firstname'];
        $this->orderby = ['surname', 'firstname'];

        return $this->getAll($cc, true, true);
    }

    public function insert($ids = null, $person_id = null, &$errors = [])
    {
        if (empty($ids) || empty($person_id)) {
            $errors[] = 'Invalid/incomplete data trying to insert Person Categories';
            return false;
        }

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $categories = [];

        foreach ($ids as $id) {
            $category = DataObject::Factory([
                'category_id' => $id,
                'person_id' => $person_id,
            ], $errors, get_class($this));

            if ($category) {
                $categories[] = $category;
            } else {
                $errors[] = 'Error validating Person Category';
                return false;
            }
        }

        $db = DB::Instance();

        foreach ($categories as $category) {
            if (! $category->save()) {
                $errors[] = $db->ErrorMsg();

                $db->FailTrans();
                $db->completeTrans();

                return false;
            }
        }

        return $db->completeTrans();
    }
}

// End of PeopleInCategories
