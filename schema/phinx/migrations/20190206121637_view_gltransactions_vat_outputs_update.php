<?php


use UzerpPhinx\UzerpMigration;

class ViewGltransactionsVatOutputsUpdate extends UzerpMigration
{
    /**
     * Add new view
     */
    public function up()
    {
        $view_name = 'gltransactions_vat_outputs';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW gltransactions_vat_outputs AS 
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
    tr1.value AS invoice_vat,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sih.base_net_value
            ELSE sih.base_net_value
        END AS invoice_net,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sil.base_tax_value
            ELSE sil.base_tax_value
        END AS vat,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sil.base_net_value
            ELSE sil.base_net_value
        END AS net,
    tr1.usercompanyid,
    a.account,
    sil.description,
    c.name AS customer,
    sih.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN si_header sih ON tr1.docref::text = sih.invoice_number::text AND tr1.type::text = sih.transaction_type::text AND tr1.source::text = 'S'::text
    JOIN si_lines sil ON sih.id = sil.invoice_id
    JOIN slmaster slm ON sih.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    JOIN taxrates tx ON sil.tax_rate_id = tx.id
    JOIN tax_statuses ts ON ts.id = sih.tax_status_id
    JOIN gl_periods glp ON glp.id = tr1.glperiods_id
UNION ALL
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
    tr1.value AS invoice_vat,
    cbt.base_net_value AS invoice_net,
    cbt.base_tax_value AS vat,
    cbt.base_net_value AS net,
    tr1.usercompanyid,
    a.account,
    cbt.description,
    c.name AS customer,
    cbt.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN cb_transactions cbt ON tr1.docref::text = cbt.reference::text AND tr1.type::text = cbt.type::text AND tr1.source::text = 'C'::text
    LEFT JOIN company c ON cbt.company_id = c.id
    JOIN taxrates tx ON cbt.tax_rate_id = tx.id
    JOIN gl_periods glp ON glp.id = tr1.glperiods_id
UNION ALL
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
    0::numeric - tr1.value AS invoice_vat,
    0::numeric - (( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_output vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text)) AS invoice_net,
    0::numeric - tr1.value AS vat,
    0::numeric - (( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_output vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text)) AS net,
    tr1.usercompanyid,
    a.account,
    tr1.comment AS description,
    ' '::character varying AS customer,
    ' '::character varying AS ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN taxrates tx ON tx.id = 1
    JOIN gl_periods glp ON glp.id = tr1.glperiods_id
    WHERE tr1.source::text = 'V'::text AND tr1.type::text = 'J'::text;
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }

    /**
     * Revert view
     */
    public function down()
    {
        $view_name = 'gltransactions_vat_outputs';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW gltransactions_vat_outputs AS 
SELECT tr1.id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    tr1.value AS invoice_vat,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sih.base_net_value
            ELSE sih.base_net_value
        END AS invoice_net,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sil.base_tax_value
            ELSE sil.base_tax_value
        END AS vat,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - sil.base_net_value
            ELSE sil.base_net_value
        END AS net,
    tr1.usercompanyid,
    a.account,
    sil.description,
    c.name AS customer,
    sih.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN si_header sih ON tr1.docref::text = sih.invoice_number::text AND tr1.type::text = sih.transaction_type::text AND tr1.source::text = 'S'::text
    JOIN si_lines sil ON sih.id = sil.invoice_id
    JOIN slmaster slm ON sih.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    JOIN taxrates tx ON sil.tax_rate_id = tx.id
    JOIN tax_statuses ts ON ts.id = sih.tax_status_id
UNION ALL
SELECT tr1.id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    tr1.value AS invoice_vat,
    cbt.base_net_value AS invoice_net,
    cbt.base_tax_value AS vat,
    cbt.base_net_value AS net,
    tr1.usercompanyid,
    a.account,
    cbt.description,
    c.name AS customer,
    cbt.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN cb_transactions cbt ON tr1.docref::text = cbt.reference::text AND tr1.type::text = cbt.type::text AND tr1.source::text = 'C'::text
    LEFT JOIN company c ON cbt.company_id = c.id
    JOIN taxrates tx ON cbt.tax_rate_id = tx.id
UNION ALL
SELECT tr1.id,
    tr1.docref,
    tr1.glaccount_id,
    tr1.glcentre_id,
    tr1.glperiods_id,
    tr1.transaction_date,
    tr1.source,
    tr1.comment,
    tr1.type,
    0::numeric - tr1.value AS invoice_vat,
    0::numeric - (( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_output vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text)) AS invoice_net,
    0::numeric - tr1.value AS vat,
    0::numeric - (( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_output vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text)) AS net,
    tr1.usercompanyid,
    a.account,
    tr1.comment AS description,
    ' '::character varying AS customer,
    ' '::character varying AS ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_output vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN taxrates tx ON tx.id = 1
    WHERE tr1.source::text = 'V'::text AND tr1.type::text = 'J'::text;
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
