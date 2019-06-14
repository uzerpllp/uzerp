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
        'outside-op-prod-group' => 'Product Group for routing outside operation purchases',
        'outside-op-mfcentre' => 'Work Centre for routing outside operations',
        'outside-op-mfresource' => 'Resource for routing outside operations',
        'allow-wo-print' => 'Allow work order printing from list view'
    ];

    protected function registerPreference()
    {
        parent::registerPreference();

        $defaultOpUnits = $this->module_preferences['default-operation-units']['preference'];
        $defaultCostBasis = $this->module_preferences['default-cost-basis']['preference'];
        $useOnlyCostBasis = $this->module_preferences['use-only-default-cost-basis']['preference'];
        $outsideOpProductGroup = $this->module_preferences['outside-op-prod-group']['preference'];
        $outsideOpMFCentre = $this->module_preferences['outside-op-mfcentre']['preference'];
        $outsideOpMFResource = $this->module_preferences['outside-op-mfresource']['preference'];
        $woPrintFromList = $this->module_preferences['allow-wo-print']['preference'];

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
            'position' => 4
        ]);

        $centres = new MFCentre;
        $list = $centres->getAll();
        $data = [];
        foreach ($list as $key => $item) {
            $data[] = ['label' => $item, 'value' => $key];
        }

        $this->preferences->registerPreference([
            'name' => 'outside-op-mfcentre',
            'display_name' => $this->module_preferences['outside-op-mfcentre']['title'],
            'type' => 'select',
            'data' => $data,
            'value' => $outsideOpMFCentre,
            'position' => 5
        ]);

        $centres = new MFResource;
        $list = $centres->getAll();
        $data = [];
        foreach ($list as $key => $item) {
            $data[] = ['label' => $item, 'value' => $key];
        }

        $this->preferences->registerPreference([
            'name' => 'outside-op-mfresource',
            'display_name' => $this->module_preferences['outside-op-mfresource']['title'],
            'type' => 'select',
            'data' => $data,
            'value' => $outsideOpMFResource,
            'position' => 6
        ]);

/*         $this->preferences->registerPreference([
            'name' => 'allow-wo-print',
            'display_name' => $this->module_preferences['allow-wo-print']['title'],
            'group_title' => 'Work Order Printing',
            'type' => 'checkbox',
            'status' => (empty($woPrintFromList) || $woPrintFromList == 'off') ? 'off' : 'on',
            'default' => 'off',
            'position' => 7
        ]); */

        $this->preferences->registerPreference([
            'name' => 'allow-wo-print',
            'display_name' => $this->module_preferences['allow-wo-print']['title'],
            'group_title' => 'Work Order Printing',
            'type' => 'select',
            'data' => [
                [
                    "label" => "Disabled",
                    "value" => "D"
                ],
                [
                    "label" => "Status New Only",
                    "value" => "N"
                ],
                [
                    "label" => "Status Released Only",
                    "value" => "R"
                ],
                [
                    "label" => "Status New and Released",
                    "value" => "A"
                ]
            ],
            'value' => (empty($woPrintFromList) || $woPrintFromList == 'D') ? 'D' : $woPrintFromList,
            'default' => 'D',
            'position' => 7
        ]);
    }
}
