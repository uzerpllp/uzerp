<?php


use UzerpPhinx\UzerpMigration;

class VatReturnOverviewSpellingFix extends UzerpMigration
{
    private $view_name = 'vat_return_overview';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW vat_return_overview AS 
select
	vr.id,
	vr.usercompanyid,
	vr.year,
	vr.tax_period,
	glp.tax_period_closed,
	max(glp.enddate) as enddate,
	vr.period_key,
	vr.finalised,
	vr.vat_due_sales,
	vr.vat_due_acquisitions,
	vr.total_vat_due,
	vr.vat_reclaimed_curr_period,
	vr.net_vat_due,
	vr.total_value_sales_ex_vat,
	vr.total_value_purchase_ex_vat,
	vr.total_value_goods_supplied_ex_vat,
	vr.total_acquisitions_ex_vat,
	vr.created,
	vr.createdby,
	vr.alteredby,
	vr.lastupdated
from vat_return vr
join gl_periods glp on glp.year = vr.year and vr.tax_period = glp.tax_period
group by 1,2,3,4,5;
VIEW;
        $this->query("DROP VIEW {$this->view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    }
    
    public function down()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW vat_return_overview AS 
select
	vr.id,
	vr.usercompanyid,
	vr.year,
	vr.tax_period,
	glp.tax_period_closed,
	max(glp.enddate) as enddate,
	vr.period_key,
	vr.finalised,
	vr.vat_due_sales,
	vr.vat_due_aquisitions,
	vr.total_vat_due,
	vr.vat_reclaimed_curr_period,
	vr.net_vat_due,
	vr.total_value_sales_ex_vat,
	vr.total_value_purchase_ex_vat,
	vr.total_value_goods_supplied_ex_vat,
	vr.total_aquisitions_ex_vat,
	vr.created,
	vr.createdby,
	vr.alteredby,
	vr.lastupdated
from vat_return vr
join gl_periods glp on glp.year = vr.year and vr.tax_period = glp.tax_period
group by 1,2,3,4,5;
VIEW;
        $this->query("DROP VIEW {$this->view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    }
}

