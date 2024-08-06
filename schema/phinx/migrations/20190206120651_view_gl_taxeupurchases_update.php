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

class ViewGlTaxeupurchasesUpdate extends UzerpMigration
{
/**
     * Update view
     */
    public function up()
    {
        $view_name = 'gl_taxeupurchases';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW gl_taxeupurchases AS 
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
    tr1.value AS vat,
    CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - pih.base_net_value
            ELSE pih.base_net_value
    END AS net,
    tr1.usercompanyid,
    a.account,
    c.name AS supplier,
    pih.ext_reference,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_eu_acquisitions vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN pi_header pih ON tr1.docref::text = pih.invoice_number::text AND tr1.type::text = pih.transaction_type::text AND tr1.source::text = 'P'::text
    JOIN plmaster plm ON pih.plmaster_id = plm.id
    JOIN company c ON plm.company_id = c.id
    JOIN tax_statuses ts ON ts.id = pih.tax_status_id AND ts.eu_tax = true
    JOIN gl_periods glp ON glp.id = tr1.glperiods_id
    WHERE tr1.source::text = 'P'::text AND (tr1.type::text = 'I'::text AND tr1.value > 0::numeric OR tr1.type::text = 'C'::text AND tr1.value < 0::numeric)
    order by 1;
VIEW_WRAP;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");

        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'vat'");
        $module_component_data = [
            [
                'name' => 'vateupurchases',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuPurchases.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => ''
            ],
            [
                'name' => 'vateupurchasescollection',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuPurchasesCollection.php',
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
        $view_name = 'gl_taxeupurchases';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW gl_taxeupurchases AS 
SELECT tr1.id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    tr1.value AS vat,
    CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - pih.base_net_value
            ELSE pih.base_net_value
    END AS net,
    tr1.usercompanyid,
    a.account,
    c.name AS supplier,
    pih.ext_reference,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_eu_acquisitions vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN pi_header pih ON tr1.docref::text = pih.invoice_number::text AND tr1.type::text = pih.transaction_type::text AND tr1.source::text = 'P'::text
    JOIN plmaster plm ON pih.plmaster_id = plm.id
    JOIN company c ON plm.company_id = c.id
    JOIN tax_statuses ts ON ts.id = pih.tax_status_id AND ts.eu_tax = true
    WHERE tr1.source::text = 'P'::text AND (tr1.type::text = 'I'::text AND tr1.value > 0::numeric OR tr1.type::text = 'C'::text AND tr1.value < 0::numeric)
    order by 1;
VIEW_WRAP;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
