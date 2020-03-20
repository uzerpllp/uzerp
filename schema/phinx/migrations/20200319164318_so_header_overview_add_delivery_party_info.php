<?php


use UzerpPhinx\UzerpMigration;

class SoHeaderOverviewAddDeliveryPartyInfo extends UzerpMigration
{
    public function up()
    {
        $view_name = 'so_headeroverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.so_headeroverview
AS
SELECT so.id,
    so.order_number,
    so.slmaster_id,
    so.del_address_id,
    so.del_partyaddress_id,
    so.order_date,
    so.due_date,
    so.despatch_date,
    so.ext_reference,
    so.currency_id,
    so.rate,
    so.net_value,
    so.twin_currency_id,
    so.twin_rate,
    so.twin_net_value,
    so.base_net_value,
    so.type,
    so.status,
    so.description,
    so.usercompanyid,
    so.despatch_action,
    so.inv_address_id,
    so.created,
    so.createdby,
    so.alteredby,
    so.lastupdated,
    so.person_id,
    slm.account_status,
    c.name AS customer,
    cum.currency,
    twc.currency AS twin_currency,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    (wa.action_name::text || '-'::text) || wa.description::text AS whaction,
    da.address AS delivery_address,
    ia.address AS invoice_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode,
    dpao.name as delivery_party_name,
    dpao.vatnumber as del_vatnumber,
    dpao.notes as del_notes,
    so.project_id,
    so.task_id,
    (prj.job_no || ' - '::text) || prj.name::text AS project
    FROM so_header so
    JOIN slmaster slm ON so.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    LEFT JOIN person p ON so.person_id = p.id
    JOIN cumaster cum ON so.currency_id = cum.id
    JOIN cumaster twc ON so.twin_currency_id = twc.id
    LEFT JOIN wh_actions wa ON so.despatch_action = wa.id
    LEFT JOIN addressoverview da ON so.del_address_id = da.id
    LEFT JOIN addressoverview ia ON so.inv_address_id = ia.id
    LEFT JOIN partyaddressoverview dpao on dpao.id = so.del_partyaddress_id
    LEFT JOIN projects prj ON so.project_id = prj.id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'so_headeroverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.so_headeroverview
AS
SELECT so.id,
    so.order_number,
    so.slmaster_id,
    so.del_address_id,
    so.order_date,
    so.due_date,
    so.despatch_date,
    so.ext_reference,
    so.currency_id,
    so.rate,
    so.net_value,
    so.twin_currency_id,
    so.twin_rate,
    so.twin_net_value,
    so.base_net_value,
    so.type,
    so.status,
    so.description,
    so.usercompanyid,
    so.despatch_action,
    so.inv_address_id,
    so.created,
    so.createdby,
    so.alteredby,
    so.lastupdated,
    so.person_id,
    slm.account_status,
    c.name AS customer,
    cum.currency,
    twc.currency AS twin_currency,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    (wa.action_name::text || '-'::text) || wa.description::text AS whaction,
    da.address AS delivery_address,
    ia.address AS invoice_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode,
    so.project_id,
    so.task_id,
    (prj.job_no || ' - '::text) || prj.name::text AS project
    FROM so_header so
    JOIN slmaster slm ON so.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    LEFT JOIN person p ON so.person_id = p.id
    JOIN cumaster cum ON so.currency_id = cum.id
    JOIN cumaster twc ON so.twin_currency_id = twc.id
    LEFT JOIN wh_actions wa ON so.despatch_action = wa.id
    LEFT JOIN addressoverview da ON so.del_address_id = da.id
    LEFT JOIN addressoverview ia ON so.inv_address_id = ia.id
    LEFT JOIN projects prj ON so.project_id = prj.id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
