<?php

/**
 * Sales Order Product Cost Search
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 **/
class socostsSearch extends BaseSearch
{

    public static function useDefault(&$search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new socostsSearch($defaults);

        // Search by Product
        $search->addSearchField('soproduct', 'SO Product contains', 'contains', [], 'basic');

        $search->setSearchData($search_data, $errors);
        return $search;
    }
}
?>
