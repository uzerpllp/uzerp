<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Create VAT adjustments overview 
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2021 uzERP LLP (support#uzerp.com). All rights reserved.
 */

class VatAdjustmentOverview extends UzerpMigration
{
    private $view_name = 'vat_adjustment_overview';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW vat_adjustment_overview AS 
select
	va.id,
	va.usercompanyid,
	va.vat_return_id,
	vr.year,
	vr.tax_period,
	va.reference,
	va.comment,
	va.vat_due_sales,
	va.vat_reclaimed_curr_period,
	va.total_value_sales_ex_vat,
	va.total_value_purchase_ex_vat,
	va.created,
	va.createdby,
	va.alteredby,
	va.lastupdated
from vat_adjustment va
join vat_return vr on vr.id = va.vat_return_id;
VIEW;
        $this->query($view);
        $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    }

    public function down()
    {
        $this->query("DROP VIEW {$this->view_name}");
    }
}
