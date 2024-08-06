<?php


use UzerpPhinx\UzerpMigration;

class MfCentreOverviewAddAvailableQty extends UzerpMigration
{
    /*
     * Add available_qty column to mf_centres
     */
    public function up()
    {
        $view_name = 'mf_centresoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.mf_centresoverview AS 
SELECT c.id,
    c.work_centre,
    c.centre,
    c.mfdept_id,
    c.usercompanyid,
    c.centre_rate,
    c.available_qty,
    c.created,
    c.createdby,
    c.alteredby,
    c.lastupdated,
    c.production_recording,
    d.dept AS mfdept
    FROM mf_centres c
    JOIN mf_depts d ON c.mfdept_id = d.id;
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    /*
     * Remove available_qty column to mf_centres
     */
    public function down()
    {
        $view_name = 'mf_centresoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.mf_centresoverview AS 
SELECT c.id,
    c.work_centre,
    c.centre,
    c.mfdept_id,
    c.usercompanyid,
    c.centre_rate,
    c.created,
    c.createdby,
    c.alteredby,
    c.lastupdated,
    c.production_recording,
    d.dept AS mfdept
    FROM mf_centres c
    JOIN mf_depts d ON c.mfdept_id = d.id;
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
