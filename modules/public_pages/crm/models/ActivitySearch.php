<?php

/**
 *  CRM Activities Search
 *
 *  @package crm
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class ActivitySearch extends BaseSearch
{

    protected $fields = array();

    public static function useDefault($search_data = null, &$errors, $defaults = null)
    {
        $search = new ActivitySearch($defaults);
        $search->addSearchField('completed', 'show_completed', 'show', 'NULL');
        $search->setOffValue('completed', 'NULL');
        $search->addSearchField('name', 'name_contains', 'contains');

        // Search by activity type
        $search->addSearchField('type_id', 'type', 'select');
        $activity_type = DataObjectFactory::Factory('Activitytype');
        $options = array('0'=>'All');
        $activity_types = $activity_type->getAll(null, false, true, '', '');
        $options +=$activity_types;
        $search->setOptions('type_id', $options);

        $search->addSearchField('assigned', 'assigned_to_me', 'hide', false);
        // $search->addSearchField('enddate', 'timeframe', 'timeframe', '');
        $search->setOnValue('assigned', EGS_USERNAME);

        $search->addSearchField('startdate', 'start date between', 'between', '', 'advanced');
        $search->addSearchField('enddate', 'end date between', 'between', '', 'advanced');
        $search->addSearchField('company', 'company_name', 'begins', '', 'advanced');
        $search->addSearchField('person', 'person', 'contains', '', 'advanced');

        $search->setSearchData($search_data, $errors);
        return $search;
    }
}
?>