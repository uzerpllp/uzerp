<?php

/**
*  @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
*  @license GPLv3 or later
*  @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
**/

class reportsSearch extends BaseSearch
{

    protected $version = '$Revision: 1.1 $';

    protected $fields = array();

    public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new reportsSearch($defaults);

// Search by Description
        $search->addSearchField(
            'description',
            'Description',
            'contains',
            '',
            'basic'
            );

// Search by Report Group
        $search->addSearchField(
            'report_group',
            'Report Group',
            'select',
            '',
            'basic'
            );

            $report = DataObjectFactory::Factory('Report');
            $options = array('' => 'All', 'NULL'    => 'Unallocated');
            $list = $report->getDistinct('report_group');
            asort($list);
            $options += $list;
            $search->setOptions('report_group', $options);

// Search by Tablename
        $search->addSearchField(
            'tablename',
            'Tablename',
            'contains',
            '',
            'advanced'
        );

// Search by Owner
        $search->addSearchField(
            'owner',
            'Owner',
            'contains',
            '',
            'advanced'
        );

        $search->setSearchData($search_data, $errors);
        return $search;
    }

}

// End of reportsSearch
