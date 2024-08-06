<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add delivery country and delivery country code to
 * tax_eu_despatches.
 * 
 * Makes delivery country information available to the
 * VAT EU despatches list.
 */
class TaxEuDespatchesAddDelCountry extends UzerpMigration
{
    public function up()
    {
        $view_name = 'tax_eu_despatches';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
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
    countries.code AS country_code,
    countries.name,
    ad.countrycode AS del_countrycode,
    ad.country as del_country,
    soh.order_number,
    uom.uom_name,
    (sdt.code::text || ' - '::text) || sdt.description::text AS delivery_terms,
    sod.usercompanyid
    FROM so_despatchlines sod
    JOIN so_header soh ON soh.id = sod.order_id
    JOIN so_lines sol ON sol.id = sod.orderline_id
    JOIN addressoverview ad ON soh.del_address_id = ad.id
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
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'tax_eu_despatches';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
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
    countries.code AS country_code,
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
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
