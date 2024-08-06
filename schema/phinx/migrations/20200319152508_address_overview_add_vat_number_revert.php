<?php


use UzerpPhinx\UzerpMigration;

class AddressOverviewAddVatNumberRevert extends UzerpMigration
{
    public function down()
    {
        $view_name = 'addressoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.addressoverview
AS
SELECT a.id,
    a.street1,
    a.street2,
    a.street3,
    a.town,
    a.county,
    a.postcode,
    a.countrycode,
    a.usercompanyid,
    a.created,
    a.createdby,
    a.alteredby,
    a.lastupdated,
    co.name AS country,
    pa.vatnumber,
    (((((((((((a.street1::text || ', '::text) || COALESCE(a.street2, ''::character varying)::text) || ', '::text) || COALESCE(a.street3, ''::character varying)::text) || ', '::text) || a.town::text) || ', '::text) || COALESCE(a.county, ''::character varying)::text) || ', '::text) || COALESCE(a.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address
    FROM address a
    JOIN partyaddress pa ON pa.address_id = a.id
    JOIN countries co ON a.countrycode = co.code;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function up()
    {
        $view_name = 'addressoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.addressoverview
AS
SELECT a.id,
    a.street1,
    a.street2,
    a.street3,
    a.town,
    a.county,
    a.postcode,
    a.countrycode,
    a.usercompanyid,
    a.created,
    a.createdby,
    a.alteredby,
    a.lastupdated,
    co.name AS country,
    (((((((((((a.street1::text || ', '::text) || COALESCE(a.street2, ''::character varying)::text) || ', '::text) || COALESCE(a.street3, ''::character varying)::text) || ', '::text) || a.town::text) || ', '::text) || COALESCE(a.county, ''::character varying)::text) || ', '::text) || COALESCE(a.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address
    FROM address a
    JOIN countries co ON a.countrycode = co.code;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}

