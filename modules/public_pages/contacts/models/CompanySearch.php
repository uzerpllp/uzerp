<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanySearch extends BaseSearch
{
    protected $version = '$Revision: 1.10 $';

    protected $fields = [];

    public static function useDefault($search_data = null, &$errors = [], $defaults = null)
    {
        $search = new CompanySearch($defaults);

        // Search by Name
        $search->addSearchField(
            'name',
            'name_contains',
            'contains'
        );

        // Search by Account Number
        $search->addSearchField(
            'accountnumber',
            'account_number_begins',
            'begins'
        );

        // Search by Is Lead
        $search->addSearchField(
            'is_lead',
            '',
            'show',
            false,
            'hidden'
        );

        // Search by Assigned to Me
        $search->addSearchField(
            'assigned_to',
            'assigned_to_me',
            'hide',
            false,
            'advanced'
        );
        $search->setOnValue('assigned_to', EGS_USERNAME);

        // Search by Active/Inactive Status
        $search->addSearchField(
            'date_inactive',
            'Show Companies',
            'null',
            'null',
            'advanced'
        );
        $options = [
            '' => 'All',
            'not null' => 'Inactive',
            'null' => 'Active',
        ];
        $search->setOptions('date_inactive', $options);

        // $search->addSearchField(
        // 	'categories',
        // 	'Category',
        // 	'null',
        // 	'null',
        // 	'advanced'
        // );
        // $categories = new Contactcategory();
        // $options = $categories->getAll();
        // $search->setOptions('categories', $options);

        // Search by Phone Number
        $search->addSearchField(
            'phone',
            'phone_number',
            'begins',
            '',
            'advanced'
        );

        // Search by Email Address
        $search->addSearchField(
            'email',
            'email',
            'contains',
            '',
            'advanced'
        );

        // Search by Town
        $search->addSearchField(
            'town',
            'town',
            'contains',
            '',
            'advanced'
        );

        // Search by Post Code
        $search->addSearchField(
            'postcode',
            'postcode',
            'contains',
            '',
            'advanced'
        );

        // Search by Country
        $search->addSearchField(
            'countrycode',
            'country',
            'select',
            '',
            'advanced'
        );

        $country = DataObjectFactory::Factory('Country');
        $countries = [
            '' => 'All',
        ];
        $countries += $country->getAll();
        $search->setOptions('countrycode', $countries);

        // Search By Company Classification
        $search->addSearchField(
            'classification_id',
            'classification',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanyClassification');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('classification_id', $options);

        // Search By Company Source
        $search->addSearchField(
            'source_id',
            'source',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanySource');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('source_id', $options);

        // Search By Industry Classification
        $search->addSearchField(
            'industry_id',
            'industry',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanyIndustry');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('industry_id', $options);

        // Search By Company Rating
        $search->addSearchField(
            'rating_id',
            'rating',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanyRating');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('rating_id', $options);

        // Search By Company Status
        $search->addSearchField(
            'status_id',
            'status',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanyStatus');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('status_id', $options);

        // Search By Company Type
        $search->addSearchField(
            'type_id',
            'type',
            'select',
            '',
            'advanced'
        );
        $model = DataObjectFactory::Factory('CompanyType');
        $options = [
            '' => 'All',
        ];
        $options += $model->getAll();
        $search->setOptions('type_id', $options);

        $search->setSearchData($search_data, $errors);

        return $search;
    }

    public static function leads($search_data = null, &$errors = [], $defaults = null)
    {
        $search = self::useDefault($search_data, $errors, $defaults);

        $search->removeSearchField('accountnumber');

        $search->removeSearchField('is_lead');

        $search->addSearchField(
            'is_lead',
            '',
            'hide',
            true,
            'hidden'
        );

        return $search;
    }
}

// End of CompanySearch
