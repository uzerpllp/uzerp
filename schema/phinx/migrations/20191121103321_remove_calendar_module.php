<?php


use UzerpPhinx\UzerpMigration;

/**
 * Remove calendar module records and tables
 */
class RemoveCalendarModule extends UzerpMigration
{
    public function up()
    {
        $this->execute('DROP TABLE calendars CASCADE');
        $this->execute('DROP TABLE calendar_events CASCADE');
        $this->execute('DROP TABLE calendar_event_attendees CASCADE');
        $this->execute('DROP TABLE calendar_shares CASCADE');

        $query = $this->query("SELECT id FROM modules WHERE name = 'calendar'");
        $cal_module_id = $query->fetchAll();

        $this->execute("DELETE FROM module_components WHERE module_id = {$cal_module_id[0]['id']}");
        $this->execute("DELETE FROM modules WHERE id = {$cal_module_id[0]['id']}");
    }
}
