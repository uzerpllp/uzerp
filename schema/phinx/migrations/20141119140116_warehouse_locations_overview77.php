<?php

use Phinx\Migration\AbstractMigration;

class WarehouseLocationsOverview77 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
		$wh_locationsoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW wh_locationsoverview AS 
 SELECT l.id,
    l.location,
    l.description,
    l.whstore_id,
    l.usercompanyid,
    l.has_balance,
    l.bin_controlled,
    l.saleable,
    l.glaccount_id,
    l.glcentre_id,
    l.created,
    l.createdby,
    l.alteredby,
    l.lastupdated,
    l.supply_demand,
    l.pickable,
    s.description AS whstore,
    (a.account::text || ' - '::text) || a.description::text AS glaccount,
    (c.cost_centre::text || ' - '::text) || c.description::text AS glcentre
   FROM wh_locations l
     JOIN wh_stores s ON l.whstore_id = s.id
     LEFT JOIN gl_accounts a ON l.glaccount_id = a.id
     LEFT JOIN gl_centres c ON l.glcentre_id = c.id;
VIEW_WRAP;

		$this->query("select deps_save_and_drop_dependencies('public', 'wh_locationsoverview')");
		$this->query('DROP VIEW wh_locationsoverview');
		$this->query($wh_locationsoverview);
		$this->query('ALTER TABLE wh_locationsoverview OWNER TO "www-data"');
		$this->query("select deps_restore_dependencies('public', 'wh_locationsoverview')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
		$wh_locationsoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW wh_locationsoverview AS 
 SELECT l.id,
    l.location,
    l.description,
    l.whstore_id,
    l.usercompanyid,
    l.has_balance,
    l.bin_controlled,
    l.saleable,
    l.glaccount_id,
    l.glcentre_id,
    l.created,
    l.createdby,
    l.alteredby,
    l.lastupdated,
    l.supply_demand,
    l.pickable,
    s.description AS whstore,
    (a.account::text || ' - '::text) || a.description::text AS glaccount,
    (c.cost_centre::text || ' - '::text) || c.description::text AS glcentre
   FROM wh_locations l
     JOIN wh_stores s ON l.whstore_id = s.id
     JOIN gl_accounts a ON l.glaccount_id = a.id
     JOIN gl_centres c ON l.glcentre_id = c.id;
VIEW_WRAP;

		$this->query("select deps_save_and_drop_dependencies('public', 'wh_locationsoverview')");
		$this->query('DROP VIEW wh_locationsoverview');
		$this->query($wh_locationsoverview);
		$this->query('ALTER TABLE wh_locationsoverview OWNER TO "www-data"');
		$this->query("select deps_restore_dependencies('public', 'wh_locationsoverview')");
    }
}
