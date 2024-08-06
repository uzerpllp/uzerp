<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add view for postponed vat accounting purchase reporting
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddViewGlTaxPvPurchases extends UzerpMigration
{
    public function up()
    {
        $view_name = 'gl_taxpvpurchases';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
    CREATE OR REPLACE VIEW gl_taxpvpurchases AS
    SELECT row_number() OVER () AS id,
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
        ts.postponed_vat_accounting AS pvtaxstatus
        FROM gl_transactions tr1
        JOIN glparams_vat_pv vi ON vi.paramvalue_id = tr1.glaccount_id
        JOIN gl_accounts a ON a.id = tr1.glaccount_id
        JOIN pi_header pih ON tr1.docref::text = pih.invoice_number::text AND tr1.type::text = pih.transaction_type::text AND tr1.source::text = 'P'::text
        JOIN plmaster plm ON pih.plmaster_id = plm.id
        JOIN company c ON plm.company_id = c.id
        JOIN tax_statuses ts ON ts.id = pih.tax_status_id AND ts.postponed_vat_accounting = true
        JOIN gl_periods glp ON glp.id = tr1.glperiods_id
        WHERE tr1.source::text = 'P'::text AND (tr1.type::text = 'I'::text AND tr1.value > 0::numeric OR tr1.type::text = 'C'::text AND tr1.value < 0::numeric)
        ORDER BY (row_number() OVER ());
VIEW_WRAP;
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
