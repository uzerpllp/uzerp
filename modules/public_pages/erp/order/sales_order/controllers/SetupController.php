<?php

/**
 *  Define and register preferences for the sales_order module
 *
 *  Preferences are stored in the module record (table 'modules')
 *  in the settings field
 *
 *  @package purchase_orders
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SetupController extends MasterSetupController
{

    protected $setup_preferences = array(
        'disable-orders-stopped' => 'Prevent new sales orders/quotes and editing for accounts on stop',
        'soline-entry-stock' => 'Show pickable stock when adding sales order lines'
    );

    protected function registerPreference()
    {
        parent::registerPreference();

        $disableSOrdersStopped = $this->module_preferences['disable-orders-stopped']['preference'];
        $showSalesStock = $this->module_preferences['soline-entry-stock']['preference'];

        $this->preferences->registerPreference(array(
            'name' => 'disable-orders-stopped',
            'display_name' => $this->module_preferences['disable-orders-stopped']['title'],
            'group_title' => 'Controls',
            'type' => 'checkbox',
            'status' => (empty($disableSOrdersStopped) || $disableSOrdersStopped == 'off') ? 'off' : 'on',
            'default' => 'off'
        ));

        $this->preferences->registerPreference(array(
            'name' => 'soline-entry-stock',
            'display_name' => $this->module_preferences['soline-entry-stock']['title'],
            'group_title' => 'Order Entry',
            'type' => 'checkbox',
            'status' => (empty($showSalesStock) || $showSalesStock == 'off') ? 'off' : 'on',
            'default' => 'off'
        ));
    }
}
?>
