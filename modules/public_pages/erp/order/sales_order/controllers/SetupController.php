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
 *  @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SetupController extends MasterSetupController
{

    protected $setup_preferences = array(
        'disable-orders-stopped' => 'Prevent new sales orders/quotes and editing for accounts on stop'
    );

    protected function registerPreference()
    {
        parent::registerPreference();

        $disableSOrdersStopped = $this->module_preferences['disable-orders-stopped']['preference'];

        $this->preferences->registerPreference(array(
            'name' => 'disable-orders-stopped',
            'display_name' => $this->module_preferences['disable-orders-stopped']['title'],
            'type' => 'checkbox',
            'status' => (empty($disableSOrdersStopped) || $disableSOrdersStopped == 'off') ? 'off' : 'on',
            'default' => 'off'
        ));
    }
}
?>
