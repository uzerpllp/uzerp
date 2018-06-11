<?php

/**
 *	WoReport - Work Order Reports Abstract Injector Class
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 */
abstract class WOReport
{

    protected $controller;

    protected $args;

    protected $data;

    protected $options;

    public function __construct(printController &$_this)
    {
        // Get the callee model (printController) to access it's methods
        $this->controller = $_this;
    }

    /**
     * Set-up the report and generate the XML
     *
     * @param array $args
     *            report args
     * @param string $xsl
     *            report definition name
     * @return boolean
     */
    protected function reportSetup($args, $xsl)
    {
        // make sure required items have been set
        if (! isset($args['model']) || ! isset($args['data'])) {
            return FALSE;
        }

        $this->args = $args;

        // set a few vars
        $MFWorkorders = $args['model'];
        $this->data = $this->args['data'];
        $model_func = [];

        // specify functions we want to execute, value will act like a field
        $model_func['MFWOStructure'] = ['requiredQty'];

        // get operations from the st_item
        $operations = new MFOperationCollection();
        $operations->loadItemOperations($MFWorkorders->stitem_id, fix_date($MFWorkorders->created, 'Y-m-d H:i:s.u'));

        $os_operations = new MFOutsideOperationCollection();
        $os_operations->loadItemOutsideOperations($MFWorkorders->stitem_id, fix_date($MFWorkorders->created, 'Y-m-d H:i:s.u'));
        
        $xml = $this->controller->generateXML([
            'model' => [
                $MFWorkorders,
                $operations,
                $os_operations
            ]
        ]);

        $this->data['printtype'] = $args['printtype'];
        $this->options = [
            'report' => $xsl, // set xsl source for MfworkordersController::printdocumentation()
            'xmlSource' => $xml
        ];

        if (isset($args['merge_file_name'])) {
            $this->options['merge_file_name'] = $args['merge_file_name'];
        }
    }

    /**
     * Called by MfworkordersController to build the report
     *
     * Implementations must call ::reportSetup($args, $xsl), where $args are passed in
     * by the caller.
     *
     * @see MfworkordersController
     * @param array $args
     */
    abstract public function buildReport($args);
}