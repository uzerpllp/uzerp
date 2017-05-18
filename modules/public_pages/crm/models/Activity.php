<?php

/**
 *  CRM Activity Model
 *
 *  @package crm
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class Activity extends DataObject
{

    protected $version = '$Revision: 1.7 $';

    protected $defaultDisplayFields = array(
        'name' => 'Name',
        'opportunity' => 'Attached to',
        'company' => 'Company',
        'person' => 'Person',
        'startdate' => 'Start Date',
        'enddate' => 'End Date',
        'type' => 'Type',
        'assigned' => 'Assigned'
    );

    function __construct($tablename = 'activities')
    {
        parent::__construct($tablename);

        $this->idField = 'id';
        $this->orderby = 'startdate';
        $this->orderdir = 'desc';

        $this->belongsTo('Activitytype', 'type_id', 'type');
        $this->belongsTo('User', 'assigned', 'username');
        $this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
        $this->belongsTo('Campaign', 'campaign_id', 'campaign');
        $this->belongsTo('Company', 'company_id', 'company');
        $this->belongsTo('Person', 'person_id', 'person', null, 'surname || \', \' || firstname');
    }
}
?>

