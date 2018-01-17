<?php

/**
 *  Define and register preferences for the manufacturing module
 *
 *  Preferences are stored in the module record (table 'modules')
 *  in the settings field
 *
 *  @package manufacturing
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SetupController extends MasterSetupController
{

    protected $setup_preferences = array(
        'default-cost-basis' => 'Default cost basis for new Stock Items',
        'use-only-default-cost-basis' => 'Use only the selected, default cost basis for new Stock Items'
    );

    protected function registerPreference()
    {
        parent::registerPreference();

        $defaultCostBasis = $this->module_preferences['default-cost-basis']['preference'];
        $useOnlyCostBasis = $this->module_preferences['use-only-default-cost-basis']['preference'];

        $this->preferences->registerPreference([
            'name' => 'default-cost-basis',
            'display_name' => $this->module_preferences['default-cost-basis']['title'],
            'type' => 'select',
            'data' => [
                [
                    "label" => "Volume",
                    "value" => "VOLUME"
                ],
                [
                    "label" => "Time",
                    "value" => "TIME"
                ]
            ],
            'value' => (empty($defaultCostBasis) || $defaultCostBasis == 'VOLUME') ? 'VOLUME' : 'TIME',
            'position' => 1
        ]);

        $this->preferences->registerPreference([
            'name' => 'use-only-default-cost-basis',
            'display_name' => $this->module_preferences['use-only-default-cost-basis']['title'],
            'type' => 'checkbox',
            'status' => (empty($useOnlyCostBasis) || $useOnlyCostBasis == 'off') ? 'off' : 'on',
            'default' => 'on',
            'position' => 2
        ]);
    }
}
