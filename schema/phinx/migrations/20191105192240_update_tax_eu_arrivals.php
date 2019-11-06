<?php


use UzerpPhinx\UzerpMigration;

class UpdateTaxEuArrivals extends UzerpMigration
{
    private $view_name = 'public.tax_eu_arrivals';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
        CREATE OR REPLACE VIEW public.tax_eu_arrivals
        AS
        SELECT por.id,
           por.gr_number,
           por.received_date,
           por.received_qty,
           por.net_mass,
           por.item_description,
           pplh.commodity_code,
           por.delivery_note,
           por.invoice_number,
           por.net_value AS receipt_net_value,
           por.currency,
           pol.base_net_value AS sterling_order_line_value,
           ( SELECT sum(pil.base_net_value) AS sum
                  FROM pi_lines pil
                 WHERE pil.order_line_id = pol.id) AS sterling_invoice_line_value,
           plm.payee_name,
           c.name AS supplier,
           countries.code as country_code,
           countries.name,
           poh.order_number,
           uom.uom_name,
           (sdt.code::text || ' - '::text) || sdt.description::text AS delivery_terms,
           por.usercompanyid
          FROM po_receivedlines por
            JOIN po_header poh ON poh.id = por.order_id
            JOIN po_lines pol ON pol.id = por.orderline_id
            JOIN plmaster plm ON plm.id = por.plmaster_id
            JOIN company c ON plm.company_id = c.id
            JOIN tax_statuses tst ON tst.id = plm.tax_status_id AND tst.eu_tax = true
            LEFT JOIN po_product_lines ppl ON ppl.id = pol.productline_id
            LEFT JOIN po_product_lines_header pplh ON pplh.id = ppl.productline_header_id
            LEFT JOIN st_uoms uom ON por.stuom_id = uom.id
            LEFT JOIN sy_delivery_terms sdt ON poh.delivery_term_id = sdt.id
            LEFT JOIN party ON c.party_id = party.id
            LEFT JOIN partyaddress ON party.id = partyaddress.party_id AND partyaddress.main
            LEFT JOIN address ON address.id = partyaddress.address_id
            LEFT JOIN countries ON address.countrycode = countries.code
         ORDER BY countries.code, por.received_date, plm.payee_name, por.item_description;
VIEW;
    $this->query("select deps_save_and_drop_dependencies('public', '{$this->view_name}')");
    $this->query("DROP VIEW {$this->view_name}");
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    $this->query("select deps_restore_dependencies('public', '{$this->view_name}')");
    }

    public function down()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
        CREATE OR REPLACE VIEW public.tax_eu_arrivals
        AS
        SELECT por.id,
           por.received_date,
           por.received_qty,
           por.net_mass,
           por.item_description,
           por.delivery_note,
           por.invoice_number,
           plm.payee_name,
           c.name AS supplier,
           poh.order_number,
           uom.uom_name,
           (sdt.code::text || ' - '::text) || sdt.description::text AS delivery_terms,
           por.usercompanyid
          FROM po_receivedlines por
            JOIN po_header poh ON poh.id = por.order_id
            JOIN plmaster plm ON plm.id = por.plmaster_id
            JOIN company c ON plm.company_id = c.id
            JOIN tax_statuses tst ON tst.id = plm.tax_status_id AND tst.eu_tax = true
            LEFT JOIN st_uoms uom ON por.stuom_id = uom.id
            LEFT JOIN sy_delivery_terms sdt ON poh.delivery_term_id = sdt.id
         ORDER BY por.received_date, plm.payee_name, por.item_description;
VIEW;
    $this->query("select deps_save_and_drop_dependencies('public', '{$this->view_name}')");
    $this->query("DROP VIEW {$this->view_name}");
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    $this->query("select deps_restore_dependencies('public', '{$this->view_name}')");
    }
}
