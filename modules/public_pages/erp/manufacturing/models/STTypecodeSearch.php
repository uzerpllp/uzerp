<?php

/**
 *  @author uzERP LLP
 *  @license GPLv3 or later
 *  @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *  uzERP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 */
class STTypecodeSearch extends BaseSearch {

    public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
        $search = new STTypecodeSearch($defaults);
        $search->addSearchField(
            'type_code',
            'type_code',
            'begins',
            '',
            'advanced'
        );
        
        $search->addSearchField(
            'description',
            'description',
            'contains',
            '',
            'advanced'
        );
        
        $search->addSearchField(
        'comp_class',
        'comp_class',
        'select',
        '',
        'basic'
        );
        $options = array('' => 'All');
        $stitem = new STItem();
        $classes = $stitem->getEnumOptions('comp_class');
        $options += $classes;
        $search->setOptions('comp_class', $options);
        
        $search->addSearchField(
            'active',
            'active',
            'select',
            '',
            'basic'
        );
        $options = array('' => 'All', 'T' => 'Yes', 'F' => 'No');
        $search->setOptions('active', $options);

        $search->setSearchData($search_data,$errors);
        return $search;
    }
}
?>