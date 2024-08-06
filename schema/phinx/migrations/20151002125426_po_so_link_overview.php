<?php
use UzerpPhinx\UzerpMigration;

class PoSoLinkOverview extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][po_headeroverview]',
        '[resources][lib_root]'
    );

    /**
     * Migrate Up.
     */
    public function up()
    {
        $po_headeroverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_headeroverview AS
 SELECT po.id,
    po.order_number,
    po.plmaster_id,
    po.del_address_id,
    po.order_date,
    po.due_date,
    po.ext_reference,
    po.currency_id,
    po.rate,
    po.net_value,
    po.twin_currency_id,
    po.twin_rate,
    po.twin_net_value,
    po.base_net_value,
    po.type,
    po.status,
    po.description,
    po.usercompanyid,
    po.date_authorised,
    po.raised_by,
    po.authorised_by,
    po.created,
    po.owner,
    po.lastupdated,
    po.alteredby,
    po.project_id,
    po.task_id,
    po.sales_order_id,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin_currency,
    pr.username AS raised_by_person,
    pa.username AS authorised_by_person,
    (p.job_no || ' - '::text) || p.name::text AS project,
    da.address AS del_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode
   FROM po_header po
     JOIN plmaster plm ON po.plmaster_id = plm.id
     JOIN company c ON plm.company_id = c.id
     JOIN cumaster cum ON po.currency_id = cum.id
     JOIN cumaster twc ON po.twin_currency_id = twc.id
     JOIN users pr ON po.raised_by::text = pr.username::text
     LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
     LEFT JOIN projects p ON po.project_id = p.id
     LEFT JOIN addressoverview da ON po.del_address_id = da.id;
VIEW_WRAP;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'po_headeroverview')");
        $this->query('DROP VIEW po_headeroverview');
        $this->query($po_headeroverview);
        $this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'po_headeroverview')");
        $this->cleanMemcache($this->cache_keys);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $po_headeroverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_headeroverview AS
 SELECT po.id,
    po.order_number,
    po.plmaster_id,
    po.del_address_id,
    po.order_date,
    po.due_date,
    po.ext_reference,
    po.currency_id,
    po.rate,
    po.net_value,
    po.twin_currency_id,
    po.twin_rate,
    po.twin_net_value,
    po.base_net_value,
    po.type,
    po.status,
    po.description,
    po.usercompanyid,
    po.date_authorised,
    po.raised_by,
    po.authorised_by,
    po.created,
    po.owner,
    po.lastupdated,
    po.alteredby,
    po.project_id,
    po.task_id,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin_currency,
    pr.username AS raised_by_person,
    pa.username AS authorised_by_person,
    (p.job_no || ' - '::text) || p.name::text AS project,
    da.address AS del_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode
   FROM po_header po
     JOIN plmaster plm ON po.plmaster_id = plm.id
     JOIN company c ON plm.company_id = c.id
     JOIN cumaster cum ON po.currency_id = cum.id
     JOIN cumaster twc ON po.twin_currency_id = twc.id
     JOIN users pr ON po.raised_by::text = pr.username::text
     LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
     LEFT JOIN projects p ON po.project_id = p.id
     LEFT JOIN addressoverview da ON po.del_address_id = da.id;
VIEW_WRAP;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'po_headeroverview')");
        $this->query('DROP VIEW po_headeroverview');
        $this->query($po_headeroverview);
        $this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'po_headeroverview')");
        $this->cleanMemcache($this->cache_keys);
    }
}
?>