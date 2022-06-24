<?php


use UzerpPhinx\UzerpMigration;

class SalesInvoiceViewAddDelTerm extends UzerpMigration
{
    public function up()
    {
        $view_name = 'si_headeroverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.si_headeroverview
AS
SELECT si.id,
    si.invoice_number,
    si.sales_order_id,
    si.slmaster_id,
    si.invoice_date,
    si.transaction_type,
    si.ext_reference,
    si.currency_id,
    si.rate,
    si.gross_value,
    si.tax_value,
    si.net_value,
    si.twin_currency_id,
    si.twin_rate,
    si.twin_gross_value,
    si.twin_tax_value,
    si.twin_net_value,
    si.base_gross_value,
    si.base_tax_value,
    si.base_net_value,
    si.payment_term_id,
    si.due_date,
    si.status,
    si.description,
    si.usercompanyid,
    si.tax_status_id,
    si.settlement_discount,
    si.delivery_note,
    si.despatch_date,
    si.date_printed,
    si.print_count,
    si.del_address_id,
    si.inv_address_id,
    si.original_due_date,
    si.created,
    si.createdby,
    si.alteredby,
    si.lastupdated,
    si.person_id,
    so.order_number AS sales_order_number,
    c.name AS customer,
    cum.currency,
    twc.currency AS twin,
    syt.description AS payment_terms,
    ts.description AS tax_status,
    slm.sl_analysis_id,
    slm.invoice_method,
    slm.edi_invoice_definition_id,
    pcm.contact AS email_invoice,
    sla.name,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    COALESCE(sl.line_count, 0::bigint) AS line_count,
    a.street1,
    a.street2,
    a.street3,
    a.town,
    a.county,
    a.postcode,
    a.countrycode,
    a.country,
    a.address AS invoice_address,
    ad.countrycode AS del_countrycode,
    ad.country AS del_country,
    CASE
    WHEN dterms.id IS NOT NULL THEN
        concat(dterms.code, ' - ', dterms.description)
    END as delivery_term,
    si.project_id,
    si.task_id,
    (prj.job_no || ' - '::text) || prj.name::text AS project
    FROM si_header si
    LEFT JOIN ( SELECT si_lines.invoice_id,
            count(*) AS line_count
            FROM si_lines
            GROUP BY si_lines.invoice_id) sl ON sl.invoice_id = si.id
    JOIN slmaster slm ON si.slmaster_id = slm.id
    LEFT JOIN so_header so ON so.id = si.sales_order_id
    LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
    JOIN company c ON slm.company_id = c.id
    LEFT JOIN person p ON si.person_id = p.id
    LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
    JOIN cumaster cum ON si.currency_id = cum.id
    JOIN cumaster twc ON si.twin_currency_id = twc.id
    JOIN tax_statuses ts ON si.tax_status_id = ts.id
    JOIN syterms syt ON si.payment_term_id = syt.id
    LEFT JOIN addressoverview a ON si.inv_address_id = a.id
    LEFT JOIN addressoverview ad ON si.del_address_id = ad.id
    LEFT JOIN projects prj ON si.project_id = prj.id
    LEFT JOIN sy_delivery_terms dterms ON dterms.id = si.delivery_term_id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }


    public function down()
    {
        $view_name = 'si_headeroverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.si_headeroverview
AS
SELECT si.id,
    si.invoice_number,
    si.sales_order_id,
    si.slmaster_id,
    si.invoice_date,
    si.transaction_type,
    si.ext_reference,
    si.currency_id,
    si.rate,
    si.gross_value,
    si.tax_value,
    si.net_value,
    si.twin_currency_id,
    si.twin_rate,
    si.twin_gross_value,
    si.twin_tax_value,
    si.twin_net_value,
    si.base_gross_value,
    si.base_tax_value,
    si.base_net_value,
    si.payment_term_id,
    si.due_date,
    si.status,
    si.description,
    si.usercompanyid,
    si.tax_status_id,
    si.settlement_discount,
    si.delivery_note,
    si.despatch_date,
    si.date_printed,
    si.print_count,
    si.del_address_id,
    si.inv_address_id,
    si.original_due_date,
    si.created,
    si.createdby,
    si.alteredby,
    si.lastupdated,
    si.person_id,
    so.order_number AS sales_order_number,
    c.name AS customer,
    cum.currency,
    twc.currency AS twin,
    syt.description AS payment_terms,
    ts.description AS tax_status,
    slm.sl_analysis_id,
    slm.invoice_method,
    slm.edi_invoice_definition_id,
    pcm.contact AS email_invoice,
    sla.name,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    COALESCE(sl.line_count, 0::bigint) AS line_count,
    a.street1,
    a.street2,
    a.street3,
    a.town,
    a.county,
    a.postcode,
    a.countrycode,
    a.country,
    a.address AS invoice_address,
    ad.countrycode AS del_countrycode,
    ad.country AS del_country,
    si.project_id,
    si.task_id,
    (prj.job_no || ' - '::text) || prj.name::text AS project
    FROM si_header si
    LEFT JOIN ( SELECT si_lines.invoice_id,
            count(*) AS line_count
            FROM si_lines
            GROUP BY si_lines.invoice_id) sl ON sl.invoice_id = si.id
    JOIN slmaster slm ON si.slmaster_id = slm.id
    LEFT JOIN so_header so ON so.id = si.sales_order_id
    LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
    JOIN company c ON slm.company_id = c.id
    LEFT JOIN person p ON si.person_id = p.id
    LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
    JOIN cumaster cum ON si.currency_id = cum.id
    JOIN cumaster twc ON si.twin_currency_id = twc.id
    JOIN tax_statuses ts ON si.tax_status_id = ts.id
    JOIN syterms syt ON si.payment_term_id = syt.id
    LEFT JOIN addressoverview a ON si.inv_address_id = a.id
    LEFT JOIN addressoverview ad ON si.del_address_id = ad.id
    LEFT JOIN projects prj ON si.project_id = prj.id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

}
