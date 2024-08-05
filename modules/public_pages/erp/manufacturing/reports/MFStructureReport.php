<?php

/**
 *	Work Order Structure Report Injector Class
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class MFStructureReport extends WOReport
{

    /**
     * Called by MfworkordersController to build the report
     *
     * @param array $args
     *            passed in by the caller
     */
    public function buildReport($args)
    {
        $xsl = 'MF_StructureReport';
        $this->reportSetup($args, $xsl);
        return json_decode((string) $this->controller->generate_output($this->data, $this->options));
    }
}
?>
