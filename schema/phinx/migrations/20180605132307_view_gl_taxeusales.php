<?php

use Phinx\Migration\AbstractMigration;

class ViewGlTaxeusales extends AbstractMigration
{
    /**
     * Update view
     */
    public function up()
    {
        $view_name = 'gl_taxeusales';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
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
VIEW_WRAP;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }

    /**
     * Restore original view
     */
    public function down()
    {
        $view_name = 'gl_taxeusales';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
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
    tr1.value AS vat,
    tr2.net,
    tr1.usercompanyid,
    a.account
    FROM gl_transactions tr1
    JOIN gl_accounts a ON a.id = tr1.glaccount_id AND a.control = true
    JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text AND tr1.source::text = tr2.source::text AND tr1.type::text = tr2.type::text
    JOIN si_header sih ON sih.invoice_number = tr1.docref::integer
    JOIN tax_statuses ts ON ts.id = sih.tax_status_id AND ts.eu_tax = true
    WHERE tr1.source::text = 'S'::text AND (tr1.type::text = 'I'::text AND tr1.value <= 0::numeric OR tr1.type::text = 'C'::text AND tr1.value >= 0::numeric);
VIEW_WRAP;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
