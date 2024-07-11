<?php

class CompanyCategorySearch extends BaseSearch
{
    protected $version = '$Revision: 1.10 $';

    protected $fields = [];

    public static function useDefault($search_data = null, &$errors = [], $defaults = null)
    {
        $search = new CompanyCategorySearch($defaults);

        // Search by Name
        $search->addSearchField(
            'company',
            'company_name_contains',
            'contains'
        );

        // Search by category
        $model = DataObjectFactory::Factory('Contactcategory');
        $options = $model->getAll();
        $search->addSearchField(
            'category_id',
            'category',
            'select',
            array_key_first($options)
        );
        $search->setOptions('category_id', $options);

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

        $search->setSearchData($search_data, $errors);

        return $search;
    }
}
