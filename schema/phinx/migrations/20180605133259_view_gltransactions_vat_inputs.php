<?php

use Phinx\Migration\AbstractMigration;

class ViewGltransactionsVatInputs extends AbstractMigration
{
    /**
     * Add new view
     */
    public function up()
    {
        $view_name = 'gltransactions_vat_inputs';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW gltransactions_vat_inputs AS 
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
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - pih.base_net_value
            ELSE pih.base_net_value
        END AS invoice_net,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - pil.base_tax_value
            ELSE pil.base_tax_value
        END AS vat,
        CASE
            WHEN tr1.type::text = 'C'::text THEN 0::numeric - pil.base_net_value
            ELSE pil.base_net_value
        END AS net,
    tr1.usercompanyid,
    a.account,
    pil.description,
    c.name AS supplier,
    pih.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    ts.eu_tax AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_input vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN pi_header pih ON tr1.docref::text = pih.invoice_number::text AND tr1.type::text = pih.transaction_type::text AND tr1.source::text = 'P'::text
    JOIN pi_lines pil ON pih.id = pil.invoice_id
    JOIN plmaster plm ON pih.plmaster_id = plm.id
    JOIN company c ON plm.company_id = c.id
    JOIN taxrates tx ON pil.tax_rate_id = tx.id
    JOIN tax_statuses ts ON ts.id = pih.tax_status_id
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
    0::numeric - cbt.base_net_value AS invoice_net,
    0::numeric - cbt.base_tax_value AS vat,
    0::numeric - cbt.base_net_value AS net,
    tr1.usercompanyid,
    a.account,
    cbt.description,
    c.name AS supplier,
    cbt.ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_input vi ON vi.paramvalue_id = tr1.glaccount_id
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
    tr1.value AS invoice_vat,
    exh.base_net_value AS invoice_net,
    exl.base_tax_value AS vat,
    exl.base_net_value AS net,
    tr1.usercompanyid,
    a.account,
    exl.item_description AS description,
    (p.firstname::text || ' '::text) || p.surname::text AS supplier,
    exh.our_reference AS ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_input vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN expenses_header exh ON tr1.docref::text = exh.expense_number::text AND tr1.source::text = 'E'::text
    JOIN expenses_lines exl ON exh.id = exl.expenses_header_id
    JOIN employees em ON exh.employee_id = em.id
    JOIN person p ON em.person_id = p.id
    JOIN taxrates tx ON exl.tax_rate_id = tx.id
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
    ( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_input vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text) AS invoice_net,
    tr1.value AS vat,
    ( SELECT tr2.value
            FROM gl_transactions tr2
            JOIN glparams_vat_input vi_1 ON vi_1.paramvalue_id <> tr2.glaccount_id
            WHERE tr2.docref::text = tr1.docref::text AND tr2.source::text = 'V'::text AND tr2.type::text = 'J'::text) AS net,
    tr1.usercompanyid,
    a.account,
    tr1.comment AS description,
    ' '::character varying AS supplier,
    ' '::character varying AS ext_reference,
    ((((tx.taxrate::text || ' - '::text) || tx.description::text) || ' at '::text) || tx.percentage::text) || '%'::text AS taxrate,
    false AS eutaxstatus
    FROM gl_transactions tr1
    JOIN glparams_vat_input vi ON vi.paramvalue_id = tr1.glaccount_id
    JOIN gl_accounts a ON a.id = tr1.glaccount_id
    JOIN taxrates tx ON tx.id = 1
    WHERE tr1.source::text = 'V'::text AND tr1.type::text = 'J'::text;
VIEW_WRAP;

        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
