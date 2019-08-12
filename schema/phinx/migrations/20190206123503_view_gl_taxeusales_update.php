<?php
/**
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
use UzerpPhinx\UzerpMigration;

class ViewGlTaxeusalesUpdate extends UzerpMigration
{
    /**
     * Update view
     */
    public function up()
    {
        $view_name = 'gl_taxeusales';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW gl_taxeusales AS 
SELECT
    row_number() OVER () AS id,
    tr1.id AS gl_id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    glp.year,
    glp.tax_period,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    0::numeric - tr1.value AS vat,
    CASE
        WHEN tr1.type::text = 'C'::text THEN 0::numeric - sih.base_net_value
        ELSE sih.base_net_value
    END as net,        
    tr1.usercompanyid,
    a.account,
    c.name AS customer,
    sih.ext_reference,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN si_header sih ON tr1.docref::text = sih.invoice_number::text AND tr1.type::text = sih.transaction_type::text AND tr1.source::text = 'S'::text
    JOIN slmaster slm ON sih.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    JOIN tax_statuses ts ON ts.id = sih.tax_status_id AND ts.eu_tax = true
    JOIN gl_periods glp ON glp.id = tr1.glperiods_id
    WHERE tr1.source::text = 'S'::text AND (tr1.type::text = 'I'::text AND tr1.value <= 0::numeric OR tr1.type::text = 'C'::text AND tr1.value >= 0::numeric);
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");

        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'vat'");
        $module_component_data = [
            [
                'name' => 'vateusales',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuSales.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => ''
            ],
            [
                'name' => 'vateusalescollection',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuSalesCollection.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => ''
            ]
        ];
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();
    }

    /**
     * Restore original view
     */
    public function down()
    {
        $view_name = 'gl_taxeusales';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW gl_taxeusales AS 
SELECT tr1.id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    0::numeric - tr1.value AS vat,
    CASE
        WHEN tr1.type::text = 'C'::text THEN 0::numeric - sih.base_net_value
        ELSE sih.base_net_value
    END as net,        
    tr1.usercompanyid,
    a.account,
    c.name AS customer,
    sih.ext_reference,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN si_header sih ON tr1.docref::text = sih.invoice_number::text AND tr1.type::text = sih.transaction_type::text AND tr1.source::text = 'S'::text
    JOIN slmaster slm ON sih.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    JOIN tax_statuses ts ON ts.id = sih.tax_status_id AND ts.eu_tax = true
    WHERE tr1.source::text = 'S'::text AND (tr1.type::text = 'I'::text AND tr1.value <= 0::numeric OR tr1.type::text = 'C'::text AND tr1.value >= 0::numeric);
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
