<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyInCategories extends DataObject
{
    protected $version = '$Revision: 1.10 $';

    protected $defaultDisplayFields = [
        'company',
        'accountnumber',
        'phone',
        'email',
        'website',
    ];

    public function __construct($tablename = 'companies_in_categories')
    {
        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'category_id';

        // Define relationships
        $this->hasOne('Company', 'company_id', 'company');
        $this->hasOne('ContactCategories', 'category_id', 'contactcategory');

        // Define field formats

        // Define validation

        // Define default values

        // Define enumerated types
    }

    #[\Override]
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

    public function getCategoryID($company_id)
    {
        $this->identifierField = 'category_id';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('company_id', '=', $company_id));

        return $this->getAll($cc);
    }

    public function getCategoryNames($company_id)
    {
        $this->identifierField = 'category';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('company_id', '=', $company_id));

        return $this->getAll($cc, null, true);
    }

    public function getCompanyID($category_id, $cc = null)
    {
        if (! $cc instanceof ConstraintChain) {
            $cc = new ConstraintChain();
        }

        if (is_array($category_id)) {
            $cc->add(new Constraint('category_id', 'in', '(' . implode(',', $category_id) . ')'));
        } else {
            $cc->add(new Constraint('category_id', '=', $category_id));
        }

        $this->idField = 'company_id';
        $this->identifierField = 'company';
        $this->orderby = 'company';

        return $this->getAll($cc, true, true);
    }

    public function insert($ids = null, $company_id = null, &$errors = [])
    {
        if (empty($ids) || empty($company_id)) {
            $errors[] = 'Invalid/incomplete data trying to insert Company Categories';
            return false;
        }

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $categories = [];

        foreach ($ids as $id) {
            $category = DataObject::Factory([
                'category_id' => $id,
                'company_id' => $company_id,
            ], $errors, get_class($this));

            if ($category) {
                $categories[] = $category;
            } else {
                $errors[] = 'Error validating Company Category';
                return false;
            }
        }

        $db = DB::Instance();

        foreach ($categories as $category) {
            if (! $category->save()) {
                $errors = $db->ErrorMsg();

                $db->FailTrans();
                $db->completeTrans();

                return false;
            }
        }

        return $db->completeTrans();
    }
}

// End of CompanyInCategories
