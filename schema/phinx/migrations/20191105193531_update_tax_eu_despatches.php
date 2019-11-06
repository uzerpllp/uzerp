<?php


use UzerpPhinx\UzerpMigration;

class UpdateTaxEuDespatches extends UzerpMigration
{
    private $view_name = 'public.tax_eu_despatches';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW'
        CREATE OR REPLACE VIEW public.tax_eu_despatches
        AS
        SELECT sod.id,
            sod.despatch_date,
            sod.despatch_qty,
            sod.net_mass,
            sol.item_description,
            splh.commodity_code,
            sod.invoice_number,
            sol.net_value AS despatch_net_value,
            sol.currency_id,
            sol.base_net_value AS sterling_order_line_value,
            ( SELECT sum(sil.base_net_value) AS sum
                FROM si_lines sil
                WHERE sil.order_line_id = sol.id) AS sterling_invoice_line_value,
            c.name AS customer,
            countries.code as country_code,
            countries.name,
            soh.order_number,
            uom.uom_name,
            (sdt.code::text || ' - '::text) || sdt.description::text AS delivery_terms,
            sod.usercompanyid
        FROM so_despatchlines sod
            JOIN so_header soh ON soh.id = sod.order_id
            JOIN so_lines sol ON sol.id = sod.orderline_id
            JOIN slmaster slm ON slm.id = sod.slmaster_id
            JOIN company c ON slm.company_id = c.id
            JOIN tax_statuses tst ON tst.id = slm.tax_status_id AND tst.eu_tax = true
            LEFT JOIN so_product_lines spl ON spl.id = sol.productline_id
            LEFT JOIN so_product_lines_header splh ON splh.id = spl.productline_header_id
            LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
            LEFT JOIN sy_delivery_terms sdt ON soh.delivery_term_id = sdt.id
            LEFT JOIN party ON c.party_id = party.id
            LEFT JOIN partyaddress ON party.id = partyaddress.party_id AND partyaddress.main
            LEFT JOIN address ON address.id = partyaddress.address_id
            LEFT JOIN countries ON address.countrycode = countries.code
        ORDER BY sod.despatch_date, c.name, sol.item_description;

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
    CREATE OR REPLACE VIEW public.tax_eu_despatches
        AS
        SELECT sod.id,
        sod.despatch_date,
        sod.despatch_qty,
        sod.net_mass,
        sol.item_description,
        sod.invoice_number,
        c.name AS customer,
        soh.order_number,
        uom.uom_name,
        (sdt.code::text || ' - '::text) || sdt.description::text AS delivery_terms,
        sod.usercompanyid
        FROM so_despatchlines sod
        JOIN so_header soh ON soh.id = sod.order_id
        JOIN so_lines sol ON sol.id = sod.orderline_id
        JOIN slmaster slm ON slm.id = sod.slmaster_id
        JOIN company c ON slm.company_id = c.id
        JOIN tax_statuses tst ON tst.id = slm.tax_status_id AND tst.eu_tax = true
        LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
        LEFT JOIN sy_delivery_terms sdt ON soh.delivery_term_id = sdt.id
    ORDER BY sod.despatch_date, c.name, sol.item_description;
VIEW;
    $this->query("select deps_save_and_drop_dependencies('public', '{$this->view_name}')");
    $this->query("DROP VIEW {$this->view_name}");
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    $this->query("select deps_restore_dependencies('public', '{$this->view_name}')");
    }
}
