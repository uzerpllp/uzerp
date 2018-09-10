<?php


use Phinx\Migration\AbstractMigration;

class MfoperationColumnChanges extends AbstractMigration
{
    /*
     * Make volume_taget not null (required)
     */
    public function up()
    {
        $mfops_require_volume_target = $this->execute("ALTER TABLE mf_operations ALTER COLUMN volume_target SET NOT NULL;");
        $mfops = $this->table('mf_operations')
        ->addColumn('outside_processing_cost', 'decimal', ['default' => 0, 'null'=> true])
        ->addColumn('type', 'string', ['length' => 1, 'default' => 'R', 'null' => false])
        ->addColumn('lead_time', 'integer', ['default' => '0', 'null' => true])
        ->addColumn('po_productline_header_id', 'biginteger', ['null' => true])
        ->addColumn('description', 'string', ['null' => true])
        ->addColumn('latest_osc', 'decimal', ['default' => '0'])
        ->addColumn('std_osc', 'decimal', ['default' => '0'])
        ->save();
    }

    /*
     * Remove requirement for volume_target to have a value
     */
    public function down() {
        $mfops_do_not_require_volume_target = $this->execute("ALTER TABLE mf_operations ALTER COLUMN volume_target DROP NOT NULL;");
        $mfops = $this->table('mf_operations')
        ->removeColumn('outside_processing_cost')
        ->removeColumn('type')
        ->removeColumn('lead_time')
        ->removeColumn('po_productline_header_id')
        ->removeColumn('description')
        ->removeColumn('latest_osc')
        ->removeColumn('std_osc')
        ->save();
    }
}
