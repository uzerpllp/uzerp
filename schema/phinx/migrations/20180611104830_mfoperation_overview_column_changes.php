<?php


use UzerpPhinx\UzerpMigration;

class MfoperationOverviewColumnChanges extends UzerpMigration
{
    /**
     * Add batch_op flag
     */
    public function up()
    {
        $view_name = 'mf_operationsoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW mf_operationsoverview AS 
SELECT o.id,
    o.op_no,
    o.start_date,
    o.end_date,
    o.remarks,
    o.stitem_id,
    o.mfcentre_id,
    o.mfresource_id,
    o.usercompanyid,
    o.volume_period,
    o.volume_uom_id,
    o.quality_target,
    o.uptime_target,
    o.volume_target,
    o.resource_qty,
    o.batch_op,
    o.std_cost,
    o.std_lab,
    o.std_ohd,
    o.latest_cost,
    o.latest_lab,
    o.latest_ohd,
    o.created,
    o.createdby,
    o.alteredby,
    o.lastupdated,
    (s.item_code::text || ' - '::text) || s.description::text AS stitem,
    s.obsolete_date,
    u.uom_name AS volume_uom,
    c.centre,
    r.description AS resource
    FROM mf_operations o
    JOIN st_items s ON o.stitem_id = s.id
    JOIN st_uoms u ON o.volume_uom_id = u.id
    JOIN mf_centres c ON o.mfcentre_id = c.id
    JOIN mf_resources r ON o.mfresource_id = r.id;
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
