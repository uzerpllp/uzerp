<?php


use UzerpPhinx\UzerpMigration;

class WorkOrderOverviewStart extends UzerpMigration
{
    public function up()
    {
        $view_name = 'mf_workordersoverview';
        $view_owner = 'www-data';
        $view = <<<VIEW_WRAP
CREATE VIEW public.mf_workordersoverview AS 
SELECT w.id,
    w.order_qty,
    w.made_qty,
    w.start_date,
    w.required_by,
    w.project_id,
    w.text1,
    w.text2,
    w.text3,
    w.orderline_id,
    w.status,
    w.stitem_id,
    w.usercompanyid,
    w.wo_number,
    w.data_sheet_id,
    w.documentation,
    w.alteredby,
    w.lastupdated,
    w.created,
    w.createdby,
    w.order_id,
    s.description AS stitem,
    s.item_code,
    s.type_code_id,
    soh.order_number,
    soh.customer,
    soh.person,
    sol.line_number,
    sol.description
    FROM mf_workorders w
    JOIN st_items s ON w.stitem_id = s.id
    LEFT JOIN so_headeroverview soh ON w.order_id = soh.id
    LEFT JOIN so_lines sol ON w.orderline_id = sol.id
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'mf_workordersoverview';
        $view_owner = 'www-data';
        $view = <<<VIEW_WRAP
CREATE VIEW public.mf_workordersoverview AS 
SELECT w.id,
    w.order_qty,
    w.made_qty,
    w.required_by,
    w.project_id,
    w.text1,
    w.text2,
    w.text3,
    w.orderline_id,
    w.status,
    w.stitem_id,
    w.usercompanyid,
    w.wo_number,
    w.data_sheet_id,
    w.documentation,
    w.alteredby,
    w.lastupdated,
    w.created,
    w.createdby,
    w.order_id,
    s.description AS stitem,
    s.item_code,
    s.type_code_id,
    soh.order_number,
    soh.customer,
    soh.person,
    sol.line_number,
    sol.description
    FROM mf_workorders w
    JOIN st_items s ON w.stitem_id = s.id
    LEFT JOIN so_headeroverview soh ON w.order_id = soh.id
    LEFT JOIN so_lines sol ON w.orderline_id = sol.id
VIEW_WRAP;

$this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
$this->query("DROP VIEW {$view_name}");
$this->query($view);
$this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
$this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
