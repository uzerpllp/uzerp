<?php

/**
 *  Define and register preferences for the purchase_order module
 *
 *  Preferences are stored in the module record (table 'modules)
 *  in the settings field
 *
 *  @package purchase_orders
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SetupController extends MasterSetupController
{

    protected $setup_preferences = array(
        'show-all-orders' => 'Show all Purchase Orders in initial view'
    );

    protected function registerPreference()
    {
        parent::registerPreference();

        $showAllOrders = $this->module_preferences['show-all-orders']['preference'];

        $this->preferences->registerPreference(array(
            'name' => 'show-all-orders',
            'display_name' => $this->module_preferences['show-all-orders']['title'],
            'type' => 'checkbox',
            'status' => (empty($showAllOrders) || $showAllOrders == 'off') ? 'off' : 'on',
            'default' => 'off'
        ));
    }
}
?>
