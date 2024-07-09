<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PeopleInCategoriesCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.5 $';

    public function __construct($do = 'PeopleInCategories', $tablename = 'people_in_categories_overview')
    {
        parent::__construct($do, $tablename);
    }

    public function getCategories($person_id)
    {
        $sh = new SearchHandler($this, false);

        $sh->addConstraint(new Constraint('person_id', '=', $person_id));

        $sh->setOrderby('category');

        $this->load($sh);
    }

    public function getPeople($category_id)
    {
        $sh = new SearchHandler($this, false);

        $sh->addConstraint(new Constraint('category_id', '=', $category_id));

        $sh->setOrderby('surname');

        $this->load($sh);
    }
}

// End of PeopleInCategoriesCollection
