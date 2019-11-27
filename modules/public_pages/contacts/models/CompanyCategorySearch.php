<?php

class CompanyCategorySearch extends BaseSearch
{
    
    protected $version = '$Revision: 1.10 $';
    
    protected $fields = array();
        
    public static function useDefault($search_data = null, &$errors, $defaults = null)
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

        $search->setOptions('category_id',$options);

        $search->setSearchData($search_data,$errors);
        
        return $search;
    }
}