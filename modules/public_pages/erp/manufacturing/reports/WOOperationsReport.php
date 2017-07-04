<?php

/**
 *	Work Order Operations Report Injector Class
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class WOOperationsReport extends WOReport
{

    /**
     * Called by MfworkordersController to build the report
     * 
     * @param array $args
     *            passed in by the caller
     */
    public function buildReport($args)
    {
        $xsl = 'wo_operations';
        $this->reportSetup($args, $xsl);
        return json_decode($this->controller->generate_output($this->data, $this->options));
    }
}