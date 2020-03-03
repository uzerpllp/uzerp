<?php


use UzerpPhinx\UzerpMigration;

/**
 * Use concat_ws function (available since postgresSQL 9.1)
 * to join address details to create column address.
 * 
 * This avoids repeated commas in the output when an address
 * detail column contains an empty value.
 */
class CompanyAddressOverviewUseConcatWs extends UzerpMigration
{
    public function up()
    {
        $view_name = 'companyaddressoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.companyaddressoverview
AS
SELECT ca.id,
    ca.street1,
    ca.street2,
    ca.street3,
    ca.town,
    ca.county,
    ca.postcode,
    ca.countrycode,
    c.id AS company_id,
    ca.name,
    ca.main,
    ca.billing,
    ca.shipping,
    ca.payment,
    ca.technical,
    c.name AS company,
    concat_ws(', ', ca.street1::text, ca.street2::text, ca.street3::text, ca.town::text, ca.county::text, ca.postcode::text, co.name::text) AS address,
    co.name AS country
    FROM companyaddress ca
    JOIN company c ON c.party_id = ca.party_id
    JOIN countries co ON ca.countrycode = co.code;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'companyaddressoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.companyaddressoverview
AS
SELECT ca.id,
    ca.street1,
    ca.street2,
    ca.street3,
    ca.town,
    ca.county,
    ca.postcode,
    ca.countrycode,
    c.id AS company_id,
    ca.name,
    ca.main,
    ca.billing,
    ca.shipping,
    ca.payment,
    ca.technical,
    c.name AS company,
    (((((((((((ca.street1::text || ', '::text) || COALESCE(ca.street2, ''::character varying)::text) || ', '::text) || COALESCE(ca.street3, ''::character varying)::text) || ', '::text) || ca.town::text) || ', '::text) || COALESCE(ca.county, ''::character varying)::text) || ', '::text) || COALESCE(ca.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address,
    co.name AS country
    FROM companyaddress ca
    JOIN company c ON c.party_id = ca.party_id
    JOIN countries co ON ca.countrycode = co.code;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
