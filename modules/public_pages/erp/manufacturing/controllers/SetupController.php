<?php

/**
 *  Define and register preferences for the manufacturing module
 *
 *  Preferences are stored in the module record (table 'modules')
 *  in the settings field
 *
 *  @see ManufacturingController::getPreferences()
 *
 *  @package manufacturing
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SetupController extends MasterSetupController
{

    protected $setup_preferences = [
        'default-operation-units' => 'Default units for operation volume/time',
        'default-cost-basis' => 'Default cost basis for new Stock Items',
        'use-only-default-cost-basis' => 'Use only the selected, default cost basis for new Stock Items',
        'outside-op-prod-group' => 'Product Group for routing outside op purchases'
    ];

    protected function registerPreference()
    {
        parent::registerPreference();

        $defaultOpUnits = $this->module_preferences['default-operation-units']['preference'];
        $defaultCostBasis = $this->module_preferences['default-cost-basis']['preference'];
        $useOnlyCostBasis = $this->module_preferences['use-only-default-cost-basis']['preference'];
        $outsideOpProductGroup = $this->module_preferences['outside-op-prod-group']['preference'];

        $this->preferences->registerPreference([
            'name' => 'default-operation-units',
            'display_name' => $this->module_preferences['default-operation-units']['title'],
            'group_title' => 'Operations',
            'type' => 'select',
            'data' => [
                [
                    "label" => "Hour",
                    "value" => "H"
                ],
                [
                    "label" => "Minute",
                    "value" => "M"
                ],
                [
                    "label" => "Second",
                    "value" => "S"
                ]
            ],
            'value' => (empty($defaultOpUnits) || $defaultOpUnits == 'H') ? 'H' : $defaultOpUnits,
            'default' => 'VOLUME',
            'position' => 1
        ]);

        $this->preferences->registerPreference([
            'name' => 'default-cost-basis',
            'display_name' => $this->module_preferences['default-cost-basis']['title'],
            'group_title' => 'Stock Item Costing',
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
            'default' => 'VOLUME',
            'position' => 2
        ]);

        $this->preferences->registerPreference([
            'name' => 'use-only-default-cost-basis',
            'display_name' => $this->module_preferences['use-only-default-cost-basis']['title'],
            'type' => 'checkbox',
            'status' => (empty($useOnlyCostBasis) || $useOnlyCostBasis == 'on') ? 'on' : 'off',
            'default' => 'on',
            'position' => 3
        ]);

        $product_groups = new STProductgroup;
        $list = $product_groups->getAll();
        $data = [];
        foreach ($list as $key => $item) {
            $data[] = ['label' => $item, 'value' => $key];
        }

        $this->preferences->registerPreference([
            'name' => 'outside-op-prod-group',
            'display_name' => $this->module_preferences['outside-op-prod-group']['title'],
            'group_title' => 'Routing Outside Operations',
            'type' => 'select',
            'data' => $data,
            'value' => $outsideOpProductGroup,
            'default' => 'VOLUME',
            'position' => 4
        ]);
    }
}
